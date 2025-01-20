<?php

namespace MulerTech\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Class Definition
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class Definition
{
    /**
     * @var class-string
     */
    private string $id;

    /**
     * @var ?class-string
     */
    private ?string $alias;

    /**
     * @var array<int|string, mixed>
     */
    private array $arguments;

    /**
     * @var bool
     */
    private bool $singleton;

    /**
     * @var object $instance
     */
    private object $instance;

    /**
     * Definition constructor.
     * @param class-string $id
     * @param class-string|null $alias
     * @param array<int|string, mixed> $arguments
     * @param bool $singleton
     */
    public function __construct(string $id, ?string $alias = null, array $arguments = [], bool $singleton = false)
    {
        $this->id = $id;
        $this->alias = $alias;
        $this->arguments = $arguments;
        $this->singleton = $singleton;
    }

    /**
     * @return class-string
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
     * @param Container $container
     * @return object
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getInstance(Container $container): object
    {
        if ($this->singleton === false && $this->hasInstance()) {
            return $this->instance;
        }

        if (!class_exists($this->id) && !interface_exists($this->id)) {
            throw new NotFoundException('The class or interface : ' . $this->id . ' not exists or not accessible.');
        }

        $reflexionClass = new ReflectionClass($this->id);

        if ($reflexionClass->isInterface() || $reflexionClass->isAbstract()) {
            if (empty($this->alias)) {
                throw new NotFoundException(
                    'The alias of this interface or abstract class was not found : ' . $this->id
                );
            }

            $reflexionClass = new ReflectionClass($this->alias);
        }

        if (is_null($constructor = $reflexionClass->getConstructor())) {
            return $this->instance = $reflexionClass->newInstance();
        }

        $arguments = $this->getInstanceArgs($container, $constructor);
        return $this->instance = $reflexionClass->newInstanceArgs($arguments);
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
     * @param Container $container
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getControllerFunction(string $function, Container $container): mixed
    {
        $controllerInstance = $this->getInstance($container);

        if (!method_exists($controllerInstance, $function)) {
            throw new NotFoundException(
                sprintf(
                    'The method "%s" of the controller "%s" doesnt exists.',
                    $function,
                    $controllerInstance::class
                )
            );
        }

        $arguments = $this->getInstanceArgs($container, new ReflectionMethod($controllerInstance, $function));

        return $controllerInstance->$function(...$arguments);
    }

    /**
     * @param Container $container
     * @param ReflectionMethod $method
     * @return array<int, mixed>
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getInstanceArgs(Container $container, ReflectionMethod $method): array
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

                if ($this->getArgumentClass($arg) === null) {
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
                if ($this->getArgumentClass($arg)->getName() === ContainerInterface::class) {
                    return $container;
                }

                return $container->get($this->getArgumentClass($arg)->getName());
            },
            $method->getParameters()
        );
    }

    /**
     * @template T of object
     * @param ReflectionParameter $argument
     * @return ReflectionClass<T>|null
     * @throws ReflectionException
     * @phpstan-ignore method.templateTypeNotInParameter
     */
    private function getArgumentClass(ReflectionParameter $argument): ?ReflectionClass
    {
        $argumentNamedType = $argument->getType();

        if (!$argumentNamedType instanceof ReflectionNamedType || $argumentNamedType->isBuiltin()) {
            return null;
        }

        /** @var class-string<T> $className */
        $className = $argumentNamedType->getName();
        return new ReflectionClass($className);
    }
}
