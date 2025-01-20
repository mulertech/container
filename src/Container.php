<?php

namespace MulerTech\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Class Container
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class Container implements ContainerInterface
{
    /**
     * @var DefinitionCollector
     */
    protected DefinitionCollector $definitionCollector;

    /**
     * @var ParameterCollector
     */
    private ParameterCollector $parameterCollector;

    /**
     * @var Container $instance of $this
     */
    private Container $instance;

    /**
     * Container constructor.
     * @param array<int, Definition> $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->definitionCollector = new DefinitionCollector($definitions);
        $this->parameterCollector = new ParameterCollector();
        $this->instance = $this;
    }

    /**
     * @param class-string $id
     * @return object|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function get(string $id): ?object
    {
        return $this->definitionCollector->getDefinition($id, $this);
    }

    /**
     * @param class-string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->definitionCollector->hasDefinition($id);
    }

    /**
     * @param class-string $id
     * @param class-string|null $alias
     * @param array<int|string, mixed> $arguments
     * @param bool $singleton
     */
    public function add(string $id, ?string $alias = null, array $arguments = [], bool $singleton = false): void
    {
        $this->definitionCollector->addDefinition(new Definition($id, $alias, $arguments, $singleton));
    }

    /**
     * @param class-string $id
     * @param object $object
     */
    public function set(string $id, object $object): void
    {
        $this->definitionCollector->setDefinition($id, $object);
    }

    /**
     * @param class-string $id
     * @param string $function
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getControllerFunc(string $id, string $function): mixed
    {
        return $this->definitionCollector->getControllerFunction($id, $function, $this);
    }

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function setParameter(string $parameter, mixed $value): void
    {
        $this->parameterCollector->set($parameter, $value);
    }

    /**
     * @param string $parameter
     * @return mixed|null
     * @throws NotFoundException
     */
    public function getParameter(string $parameter): mixed
    {
        return $this->parameterCollector->get($parameter);
    }

    /**
     * @param string $parameter
     * @return bool
     */
    public function hasParameter(string $parameter): bool
    {
        return $this->parameterCollector->has($parameter);
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws NotFoundException
     */
    public function replaceReferences(mixed $value): mixed
    {
        $this->parameterCollector->replaceReferences($value);
        return $value;
    }

    /**
     * Get the instance of this container.
     * @return Container
     */
    public function getInstance(): Container
    {
        return $this->instance;
    }
}
