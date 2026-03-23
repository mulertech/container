<?php

namespace MulerTech\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class Container.
 *
 * @author Sébastien Muler
 */
class Container implements ContainerInterface
{
    protected DefinitionCollector $definitionCollector;

    private ParameterCollector $parameterCollector;

    /**
     * @var Container of $this
     */
    private Container $instance;

    /**
     * Container constructor.
     *
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
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function get(string $id): ?object
    {
        return $this->definitionCollector->getDefinition($id, $this);
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        return $this->definitionCollector->hasDefinition($id);
    }

    /**
     * @param class-string             $id
     * @param class-string|null        $alias
     * @param array<int|string, mixed> $arguments
     */
    public function add(string $id, ?string $alias = null, array $arguments = [], bool $singleton = false): void
    {
        $this->definitionCollector->addDefinition(new Definition($id, $alias, $arguments, $singleton));
    }

    /**
     * @param class-string $id
     */
    public function set(string $id, object $object): void
    {
        $this->definitionCollector->setDefinition($id, $object);
    }

    /**
     * @param class-string $id
     *
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getControllerFunc(string $id, string $function): mixed
    {
        return $this->definitionCollector->getControllerFunction($id, $function, $this);
    }

    public function setParameter(string $parameter, mixed $value): void
    {
        $this->parameterCollector->set($parameter, $value);
    }

    /**
     * @return mixed|null
     *
     * @throws NotFoundException
     */
    public function getParameter(string $parameter): mixed
    {
        return $this->parameterCollector->get($parameter);
    }

    public function hasParameter(string $parameter): bool
    {
        return $this->parameterCollector->has($parameter);
    }

    /**
     * @throws NotFoundException
     */
    public function replaceReferences(mixed $value): mixed
    {
        return $this->parameterCollector->replaceReferences($value);
    }

    /**
     * Get the instance of this container.
     */
    public function getInstance(): Container
    {
        return $this->instance;
    }
}
