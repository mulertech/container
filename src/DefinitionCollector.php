<?php

namespace MulerTech\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class DefinitionCollector.
 *
 * @author Sébastien Muler
 */
class DefinitionCollector
{
    /**
     * @var array<class-string, Definition>
     */
    private array $definitions = [];

    /**
     * @param array<int, Definition> $definitions
     */
    public function __construct(array $definitions = [])
    {
        foreach ($definitions as $definition) {
            $this->addDefinition($definition);
        }
    }

    /**
     * @template Id of object
     *
     * @param class-string<Id> $id
     *
     * @throws NotFoundException
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getDefinition(string $id, Container $container): ?object
    {
        return $this->getOrCreateDefinition($id)->getInstance($container);
    }

    /**
     * @template Id of object
     *
     * @param class-string<Id> $id
     */
    public function setDefinition(string $id, object $object): void
    {
        $definition = new Definition($id);
        $definition->setInstance($object);
        $this->definitions[$id] = $definition;
    }

    /**
     * @template Id of object
     *
     * @param class-string<Id> $id
     */
    public function hasDefinition(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * @template Id of object
     *
     * @param class-string<Id> $id
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getControllerFunction(string $id, string $function, Container $container): mixed
    {
        return $this->getOrCreateDefinition($id)->getControllerFunction($function, $container);
    }

    public function addDefinition(Definition $definition): void
    {
        $this->definitions[$definition->getId()] = $definition;
    }

    /**
     * @template Id of object
     *
     * @param class-string<Id> $id
     */
    private function getOrCreateDefinition(string $id): Definition
    {
        if (!isset($this->definitions[$id])) {
            $this->definitions[$id] = new Definition($id);
        }

        return $this->definitions[$id];
    }
}
