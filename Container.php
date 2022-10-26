<?php


namespace MulerTech\Container;

use Psr\Container\ContainerInterface;
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
    protected $definitionCollector;

    /**
     * @var ParameterCollector
     */
    private $parameterCollector;

    /**
     * @var Container $instance of $this
     */
    private $instance;

    /**
     * Container constructor.
     * @param Definition[] $definitions
     */
    public function __construct(array $definitions = [])
    {
        if (!isset($this->definitionCollector)) {
            $this->definitionCollector = new DefinitionCollector($definitions);
        }
        if (!isset($this->parameterCollector)) {
            $this->parameterCollector = new ParameterCollector();
        }
        if (!isset($this->instance)) {
            $this->instance = $this;
        }
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function get($id): object
    {
        return $this->definitionCollector->getDefinition($id, $this);
    }

    /**
     * @inheritDoc
     */
    public function has($id): bool
    {
        return $this->definitionCollector->hasDefinition($id);
    }

    /**
     * @param string $id
     * @param string|null $alias
     * @param array $arguments
     * @param bool $singleton
     */
    public function add(string $id, string $alias = null, array $arguments = [], bool $singleton = false): void
    {
        $this->definitionCollector->addDefinition(new Definition($id, $alias, $arguments, $singleton));
    }

    /**
     * @param string $id
     * @param object $object
     */
    public function set(string $id, object $object): void
    {
        $this->definitionCollector->setDefinition($id, $object);
    }

    /**
     * @param string $id
     * @param string $function
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getControllerFunc(string $id, string $function)
    {
        return $this->definitionCollector->getControllerFunction($id, $function, $this);
    }

    /**
     * @param string $parameter
     * @param mixed $value
     */
    public function setParameter(string $parameter, $value): void
    {
        $this->parameterCollector->set($parameter, $value);
    }

    /**
     * @param string $parameter
     * @return mixed|null
     * @throws NotFoundException
     */
    public function getParameter(string $parameter)
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
    public function replaceReferences($value)
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