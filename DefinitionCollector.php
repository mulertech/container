<?php


namespace mtphp\Container;

use Psr\Container\ContainerInterface;
use ReflectionException;

/**
 * Class DefinitionCollector
 * @package mtphp\Container
 * @author Sébastien Muler
 */
class DefinitionCollector
{
    /**
     * @var Definition[]
     */
    private $definitions;

    public function __construct(array $definitions = [])
    {
        if (!empty($definitions) && is_array($definitions)) {
            foreach ($definitions as $definition) {
                $this->addDefinition($definition);
            }
        }
    }

    /**
     * @param $id
     * @param ContainerInterface $container
     * @return object
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getDefinition($id, ContainerInterface $container): object
    {
        if (!isset($this->definitions[$id])) {
            $this->definitions[$id] = new Definition($id);
        }

        return $this->definitions[$id]->getInstance($container);
    }

    /**
     * @param $id
     * @param object $object
     */
    public function setDefinition($id, object $object): void
    {
        $definition = new Definition($id);
        $definition->setInstance($object);
        $this->definitions[$id] = $definition;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasDefinition($id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * @param string $id
     * @param string $function
     * @param ContainerInterface $container
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getControllerFunction(string $id, string $function, ContainerInterface $container)
    {
        if (!isset($this->definitions[$id])) {
            $this->definitions[$id] = new Definition($id);
        }

        return $this->definitions[$id]->getControllerFunction($function, $container);
    }

    /**
     * @param Definition $definition
     */
    public function addDefinition(Definition $definition): void
    {
        $this->definitions[$definition->getId()] = $definition;
    }

}