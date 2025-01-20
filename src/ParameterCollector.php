<?php

namespace MulerTech\Container;

use RuntimeException;

/**
 * Class ParameterCollector
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class ParameterCollector
{
    private const string PARAMETER_TAG = '%';
    private const string PREG_MATCH_ENV = '/^env\((.+)\)$/';
    /**
     * @var array<int|string, mixed> $parameters
     */
    private array $parameters = [];

    /**
     * @param string $parameter
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $parameter): mixed
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
    public function set(string $parameter, mixed $value): void
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * @param string $parameter
     * @return bool
     */
    public function has(string $parameter): bool
    {
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
    private function generateParameter(string $parameter): mixed
    {
        $value = $this->parameters[$parameter] ?? $parameter;
        return $this->replaceReferences($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws NotFoundException
     */
    public function replaceReferences(mixed &$value): mixed
    {
        if (is_array($value)) {
            array_walk_recursive($value, [$this, 'replaceReferences']);
            return $value;
        }

        if (is_string($value)) {
            $value = $this->replaceParameterReferences($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string|array<int|string, mixed>|object
     * @throws NotFoundException
     */
    public function replaceParameterReferences(string $value): object|array|string
    {
        $value = $this->replaceEnv($value);

        if (preg_match_all(
            '/' . self::PARAMETER_TAG . '([A-Za-z0-9]+([-_.]?[A-Za-z0-9]+)+)' . self::PARAMETER_TAG . '/',
            $value,
            $matches
        )) {
            return $this->putParameterReference($value, $matches);
        }

        return $value;
    }

    /**
     * @param string $originalValue
     * @param array<int, mixed> $matches
     * @return string|array<int|string, mixed>|object
     * @throws NotFoundException
     */
    private function putParameterReference(string $originalValue, array $matches): string|array|object
    {
        foreach ($matches[0] as $key => $reference) {
            $parameterKey = $matches[1][$key];

            if (!is_string($parameterKey) || !$this->has($parameterKey)) {
                continue;
            }

            $newValue = $this->get($parameterKey);

            if (is_string($newValue)) {
                $originalValue = str_replace($reference, $this->get($parameterKey), $originalValue);
                continue;
            }

            return $newValue;
        }

        return $originalValue;
    }

    /**
     * @param string $value
     * @return string
     */
    private function replaceEnv(string $value): string
    {
        if (preg_match(self::PREG_MATCH_ENV, $value, $result)) {
            $env = $this->getEnv($result[1]);

            if ($env === false) {
                return $value;
            }

            return $env;
        }

        return $value;
    }

    /**
     * @param string $name
     * @return false|string
     */
    private function getEnv(string $name): false|string
    {
        return getenv($name);
    }
}
