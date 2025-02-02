<?php

namespace MulerTech\Container;

/**
 * Class ParameterCollector
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class ParameterCollector
{
    private const string PREG_MATCH_PARAMETER = '/%([A-Za-z0-9]+([-_.]?[A-Za-z0-9]+)+)%/';
    // For the sake of portability (and sanity), environment variable names must :
    // consist solely of letters, digits, and the underscore ( _ ) and must not begin with a digit.
    private const string PREG_MATCH_ENV = '/env\(([a-zA-Z_]+[\w_]*)\)/';

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
                sprintf('The "%s" parameter was not found in ParameterCollector.', $parameter)
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
        return isset($this->parameters[$parameter]);
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
        // The new $value may not be a string anymore
        if (is_string($value)) {
            $value = $this->replaceEnvReferences($value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string|array<int|string, mixed>|object
     * @throws NotFoundException
     */
    private function replaceParameterReferences(string $value): object|array|string
    {
        if (preg_match_all(self::PREG_MATCH_PARAMETER, $value, $matches)) {
            foreach ($matches[1] as $parameterKey) {
                if (!$this->has($parameterKey)) {
                    continue;
                }

                $newValue = $this->get($parameterKey);

                if (!is_string($newValue)) {
                    return $newValue;
                }

                $value = str_replace("%$parameterKey%", $newValue, $value);
            }
        }
        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    private function replaceEnvReferences(string $value): string
    {
        if (preg_match_all(self::PREG_MATCH_ENV, $value, $matches)) {
            foreach ($matches[1] as $envKey) {
                $envValue = $this->getEnv($envKey);

                if ($envValue !== false) {
                    $value = str_replace("env($envKey)", $envValue, $value);
                }
            }
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
