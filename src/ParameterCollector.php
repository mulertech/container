<?php

namespace MulerTech\Container;

/**
 * Class ParameterCollector.
 *
 * @author Sébastien Muler
 */
class ParameterCollector
{
    private const string PREG_MATCH_PARAMETER = '/%([A-Za-z0-9]+([-_.]?[A-Za-z0-9]+)+)%/';
    // For the sake of portability (and sanity), environment variable names must :
    // consist solely of letters, digits, and the underscore ( _ ) and must not begin with a digit.
    private const string PREG_MATCH_ENV = '/env\(([a-zA-Z_]+[\w_]*)\)/';

    /**
     * @var array<int|string, mixed>
     */
    private array $parameters = [];

    /**
     * @throws NotFoundException
     */
    public function get(string $parameter): mixed
    {
        if (!$this->has($parameter)) {
            throw new NotFoundException(sprintf('The "%s" parameter was not found in ParameterCollector.', $parameter));
        }

        return $this->generateParameter($parameter);
    }

    public function set(string $parameter, mixed $value): void
    {
        $this->parameters[$parameter] = $value;
    }

    public function has(string $parameter): bool
    {
        return isset($this->parameters[$parameter]);
    }

    /**
     * @throws NotFoundException
     */
    private function generateParameter(string $parameter): mixed
    {
        $value = $this->parameters[$parameter] ?? $parameter;

        return $this->replaceReferences($value);
    }

    /**
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
     * @throws NotFoundException
     */
    private function replaceParameterReferences(string $value): mixed
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

    private function replaceEnvReferences(string $value): string
    {
        if (preg_match_all(self::PREG_MATCH_ENV, $value, $matches)) {
            foreach ($matches[1] as $envKey) {
                $envValue = $this->getEnv($envKey);

                if (false !== $envValue) {
                    $value = str_replace("env($envKey)", $envValue, $value);
                }
            }
        }

        return $value;
    }

    private function getEnv(string $name): false|string
    {
        return getenv($name);
    }
}
