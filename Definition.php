<?php


namespace MulerTech\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Definition
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class Definition
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var ?string
     */
    private $alias;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var bool
     */
    private $singleton;

    /**
     * @var object
     */
    private $instance;

    /**
     * Definition constructor.
     * @param string $id
     * @param string|null $alias
     * @param array $arguments
     * @param bool $singleton
     */
    public function __construct(string $id, string $alias = null, array $arguments = [], bool $singleton = false)
    {
        $this->id = $id;
        $this->alias = $alias;
        $this->arguments = $arguments;
        $this->singleton = $singleton;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function hasInstance(): bool
    {
        return isset($this->instance);
    }

    /**
     * @param ContainerInterface $container
     * @return object|null
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getInstance(ContainerInterface $container): ?object
    {
        if ($this->singleton === true || !$this->hasInstance()) {
            if (!class_exists($this->id) && !interface_exists($this->id)) {
                throw new NotFoundException('The class or interface : ' . $this->id . ' not exists or not accessible.');
            }

            $reflexion_class = new ReflectionClass($this->id);

            if ($reflexion_class->isInterface() || $reflexion_class->isAbstract()) {
                if (empty($this->alias)) {
                    throw new NotFoundException(
                        'The alias of this interface or abstract class was not found : ' . $this->id
                    );
                }

                $reflexion_class = new ReflectionClass($this->alias);
            }

            if (is_null($constructor = $reflexion_class->getConstructor())) {
                return $this->instance = $reflexion_class->newInstance();
            }

            $arguments = $this->getInstanceArgs($container, $constructor);
            $this->instance = $reflexion_class->newInstanceArgs($arguments);
        }

        return $this->instance ?? null;
    }

    /**
     * @param object $object
     */
    public function setInstance(object $object): void
    {
        $this->instance = $object;
    }

    /**
     * @param string $function
     * @param ContainerInterface $container
     * @return mixed
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getControllerFunction(string $function, ContainerInterface $container)
    {
        $controller_instance = $this->getInstance($container);
        if (is_null($controller_instance)) {
            return null;
        }
        $function_arguments = $this->getInstanceArgs($container, new ReflectionMethod($controller_instance, $function));
        return call_user_func_array([$controller_instance, $function], $function_arguments);
    }

    /**
     * @param ContainerInterface $container
     * @param ReflectionMethod $method
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException
     */
    private function getInstanceArgs(ContainerInterface $container, ReflectionMethod $method): array
    {
        return array_map(
            function ($arg) use ($container) {
                if (isset($this->arguments[$arg->getName()])) {
                    return $container->replaceReferences($this->arguments[$arg->getName()]);
                }

                /**
                 * If the arguments given was a classic array, give them for each arg,
                 * for give not all arguments you need to give this with this name.
                 */
                if (!empty($this->arguments) && isset($this->arguments[0])) {
                    $argument = array_shift($this->arguments);
                    return (is_string($argument)) ? $container->replaceReferences($argument) : $argument;
                }

                if ($arg->getClass() === null) {
                    if ($arg->isDefaultValueAvailable()) {
                        return $arg->getDefaultValue();
                    }

                    throw new NotFoundException(
                        sprintf(
                            'The class or interface : %s has a missing argument named : %s.',
                            $this->id,
                            $arg->getName()
                        )
                    );
                }

                //If argument is a container interface give it the $container
                if ($arg->getClass()->getName() === ContainerInterface::class) {
                    return $container;
                }

                return $container->get($arg->getClass()->getName());
            },
            $method->getParameters()
        );
    }

}