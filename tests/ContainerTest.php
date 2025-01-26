<?php

namespace MulerTech\Container\Tests;

use MulerTech\Container\Container;
use MulerTech\Container\Definition;
use MulerTech\Container\Loader;
use MulerTech\Container\Loader\LoaderNotFoundException;
use MulerTech\Container\Loader\YamlLoader;
use MulerTech\Container\NotFoundException;
use MulerTech\Container\Tests\FakeClass\WithConstruct;
use MulerTech\FileManipulation\PathManipulation;
use PHPUnit\Framework\TestCase;
use MulerTech\Container\Tests\FakeClass\Bar;
use MulerTech\Container\Tests\FakeClass\ControllerFake;
use MulerTech\Container\Tests\FakeClass\ControllerWithConstructFake;
use MulerTech\Container\Tests\FakeClass\Foo;
use MulerTech\Container\Tests\FakeClass\FooInterface;
use MulerTech\Container\Tests\FakeClass\Origin;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Class ContainerTest
 * @package MulerTech\Container\Tests
 * @author Sébastien Muler
 */
class ContainerTest extends TestCase
{
    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetSimpleClass(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Foo.php');
        $container = new Container();
        $foo = $container->get(Foo::class);
        static::assertInstanceOf(Foo::class, $foo);
    }

    public function testGetClassWithSimpleConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'WithConstruct.php');
        $container = new Container();
        $container->add(WithConstruct::class, null, ['test']);
        $withConstruct = $container->get(WithConstruct::class);
        static::assertInstanceOf(WithConstruct::class, $withConstruct);
    }

    public function testGetClassWithSimpleConstructWithoutGivenParameter(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'WithConstruct.php');
        $container = new Container();
        $container->add(WithConstruct::class);
        $withConstruct = $container->get(WithConstruct::class);
        static::assertInstanceOf(WithConstruct::class, $withConstruct);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetClassWithClassAndVariableOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Bar.php');
        $container = new Container();
        $bar = $container->get(Bar::class);
        static::assertInstanceOf(Bar::class, $bar);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetClassWithClassAndVariableOnConstructGiven(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Bar.php');
        $container = new Container();
        $container->add(Bar::class, null, [new Foo(), 'test']);
        $bar = $container->get(Bar::class);
        static::assertInstanceOf(Bar::class, $bar);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetClassWithClassAndNeededVariableNotGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetClassWithInterfaceAndNeededVariableNotGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(FooInterface::class, Foo::class);
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetWithoutNeededInterfaceWithNamedVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetWithNeededInterfaceWithNamedVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        static::assertInstanceOf(Origin::class, $origin);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetWithNeededInterfaceWithVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        static::assertInstanceOf(Origin::class, $origin);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetWithNeededInterfaceWithReferenceVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->setParameter('test', 'its ok');
        $container->add(Origin::class, null, ['test' => '%test%']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        /** @var Origin $origin */
        static::assertEquals('its ok', $origin->getTest());
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testGetFalseClass(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get(Fooo::class);
    }

    /**
     * @return void
     */
    public function testHasFalseClass(): void
    {
        $container = new Container();
        $has = $container->has(Fooo::class);
        self::assertEquals(null, $has);
    }

    /**
     * @return void
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function testControllerFunction(): void
    {
        $container = new Container();
        $return = $container->getControllerFunc(ControllerFake::class, 'fake');
        self::assertEquals('fake function in controller', $return);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testControllerFunctionDoesntExists(): void
    {
        $container = new Container();
        $this->expectExceptionMessage('The method "doesntExists" of the controller "MulerTech\Container\Tests\FakeClass\ControllerFake" doesn\'t exist.');
        $container->getControllerFunc(ControllerFake::class, 'doesntExists');
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testControllerWithConstruct(): void
    {
        $container = new Container();
        $return = $container->getControllerFunc(ControllerWithConstructFake::class, 'fake');
        self::assertEquals('fake function in controller', $return);
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testControllerWithConstructAndContainerUpdatedIntoIt(): void
    {
        $container = new Container();
        $container->getControllerFunc(ControllerWithConstructFake::class, 'fake');
        self::assertTrue($container->has(Foo::class));
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testAddDefinitions(): void
    {
        $container = new Container([new Definition(FooInterface::class, Foo::class)]);
        $foo = $container->get(FooInterface::class);
        self::assertInstanceOf(Foo::class, $foo);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testSetGetParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', 'value');
        self::assertEquals('value', $container->getParameter('test'));
    }

    /**
     * @return void
     */
    public function testSetHasParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', 'value');
        self::assertTrue($container->hasParameter('test'));
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testGetNotFoundParameter(): void
    {
        $this->expectExceptionMessage('Class ParameterCollector, function get. The "test" parameter was not found.');
        $container = new Container();
        $container->getParameter('test');
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testParameterIntoParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', '%othervalue%');
        $container->setParameter('othervalue', 'test an other value');
        self::assertEquals('test an other value', $container->getParameter('test'));
    }

    public function testParameterIntoParameterNotGiven(): void
    {
        $container = new Container();
        $container->setParameter('test', '%othervalue%');
        self::assertEquals($container->getParameter('test'), '%othervalue%');
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testParametersIntoParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', '%oth.er-val_ue%.another-value:%value2%');
        $container->setParameter('oth.er-val_ue', 'test an other value');
        $container->setParameter('value2', 'a good new value two');
        self::assertEquals(
            'test an other value.another-value:a good new value two',
            $container->getParameter('test')
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testParametersIntoArrayParameter(): void
    {
        $container = new Container();
        $container->setParameter(
            'test',
            ['%oth.er-val_ue%.another-value:%value2%', 'a simple replacement:%value2%']
        );
        $container->setParameter('oth.er-val_ue', 'test an other value');
        $container->setParameter('value2', 'a good new value two');
        self::assertEquals(
            ['test an other value.another-value:a good new value two', 'a simple replacement:a good new value two'],
            $container->getParameter('test')
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testParametersIntoArrayParameterWithArray(): void
    {
        $container = new Container();
        $container->setParameter(
            'test',
            ['%oth.er-val_ue%.another-value:%value2%', ['a simple replacement:%value2%']]
        );
        $container->setParameter('oth.er-val_ue', 'test an other value');
        $container->setParameter('value2', 'a good new value two');
        self::assertEquals(
            ['test an other value.another-value:a good new value two', ['a simple replacement:a good new value two']],
            $container->getParameter('test')
        );
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testParametersIntoObjectParameter(): void
    {
        $container = new Container();
        $container->setParameter('object', '%test%');
        $container->setParameter('test', new class {
            public string $value = 'test';
        });
        self::assertEquals('test', $container->getParameter('object')->value);
    }

    /**
     * @return void
     */
    public function testBadLoader(): void
    {
        $this->expectException(LoaderNotFoundException::class);
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(Loader\BadLoader::class);
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testLoaderYaml(): void
    {
        $container = new Container();
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(YamlLoader::class)
            ->loadParameters($container);
        self::assertEquals(3, $container->getParameter('config1.one'));
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testLoaderYamlArray(): void
    {
        $container = new Container();
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(YamlLoader::class)
            ->loadParameters($container);
        self::assertEquals(
            [0 => 'onevaluelist', 1 => 'secondvaluelist', 2 => 'thirdvaluelist'],
            $container->getParameter('config3')
        );
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function testSetSimpleClass(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Foo.php');
        $container = new Container();
        $container->set(Foo::class, new Foo());
        static::assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    /**
     * @return void
     */
    public function testGetInstance(): void
    {
        $container = new Container();
        $container->set(Foo::class, new Foo());
        self::assertTrue($container->getInstance()->has(Foo::class));
    }

    /**
     * @return void
     */
    public function testHasEnvParameter(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', 'env(test)');
        self::assertTrue($container->hasParameter('test_env'));
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testGetEnvParameter(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', 'env(test)');
        self::assertEquals('hello world', $container->getParameter('test_env'));
    }

    public function testGetSeveralEnvParameterOnSameLine(): void
    {
        putenv('test=hello world');
        putenv('test2=hello world 2');
        $container = new Container();
        $container->setParameter('test_env', 'env(test) env(test2)');
        self::assertEquals('hello world hello world 2', $container->getParameter('test_env'));
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testGetEnvParameterInArray(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', ['key' => 'env(test)']);
        self::assertEquals(['key' => 'hello world'], $container->getParameter('test_env'));
    }

    public function testGetEnvParameterNotSet(): void
    {
        $container = new Container();
        $container->setParameter('test_env', 'env(test_not_set)');
        self::assertEquals('env(test_not_set)', $container->getParameter('test_env'));
    }
}