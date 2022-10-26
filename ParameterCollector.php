<?php

namespace MulerTech\Container;

/**
 * Class ParameterCollector
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class ParameterCollector
{

    private const PREG_MATCH_ENV = '/^env\((.+)\)$/';
    private $parameters = [];

    /**
     * @param string $parameter
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $parameter)
    {
        if (!$this->has($parameter)) {
            throw new NotFoundException(
                sprintf('Class ParameterCollector, function get. The "%s" parameter was not found.', $parameter)
            );
        }

        return $this->generateParameter($parameter);
    }

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function set(string $parameter, $value): void
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * @param string $parameter
     * @return bool
     */
    public function has(string $parameter): bool
    {
        if (preg_match(self::PREG_MATCH_ENV, $parameter, $result)) {
            return $this->getEnv($result[1]) !== false;
        }

        if (isset($this->parameters[$parameter])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $parameter
     * @return mixed
     * @throws NotFoundException
     */
    private function generateParameter(string $parameter)
    {
        $value = $this->parameters[$parameter] ?? $parameter;
        return $this->replaceReferences($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws NotFoundException
     */
    public function replaceReferences(&$value)
    {
        if (is_array($value)) {
            array_walk_recursive($value, [$this, 'replaceReferences']);
        } elseif (is_string($value)) {
            $value = $this->replaceParameterReferences($value);
        }
        return $value;
    }

    /**
     * @param $value (string input but mixed output)
     * @return string|array|object
     * @throws NotFoundException
     */
    public function replaceParameterReferences($value)
    {
        $tag = '%';
        $value = $this->replaceEnv($value);
        if (preg_match_all(
            '/' . $tag . '([A-Za-z0-9]+([-_.]?[A-Za-z0-9]+)+)' . $tag . '/',
            $value,
            $results
        )) {
            foreach ($results[0] as $key => $result) {
                if ($this->has($results[1][$key])) {
                    $newValue = $this->get($results[1][$key]);
                    if (is_array($newValue) || is_object($newValue)) {
                        if ($tag . $results[1][$key] . $tag === $value) {
                            return $newValue;
                        }
                        throw new \RuntimeException(sprintf('Class : ParameterCollector, function : replaceParameterReferences. The container reference (%s) in this value (%s) refers to an array (%s), this can\'t be put into a string.', $results[1][$key], $value, $newValue));
                    }
                    $value = str_replace($result, $this->get($results[1][$key]), $value);
                }
            }
        }
        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private function replaceEnv(string $value): string
    {
        if (preg_match(self::PREG_MATCH_ENV, $value, $result)) {
            return $this->getEnv($result[1]);
        }
        return $value;
    }

    /**
     * @param string $name
     * @return array|false|string
     */
    private function getEnv(string $name)
    {
        return getenv($name);
    }
}