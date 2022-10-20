<?php

namespace mtphp\Container\Tests;

use mtphp\Container\Container;
use mtphp\Container\Definition;
use mtphp\Container\Loader;
use mtphp\Container\NotFoundException;
use mtphp\Database\NonRelational\DocumentStore\PathManipulation;
use PHPUnit\Framework\TestCase;
use mtphp\Container\Tests\FakeClass\Bar;
use mtphp\Container\Tests\FakeClass\ControllerFake;
use mtphp\Container\Tests\FakeClass\ControllerWithConstructFake;
use mtphp\Container\Tests\FakeClass\Foo;
use mtphp\Container\Tests\FakeClass\FooInterface;
use mtphp\Container\Tests\FakeClass\Origin;

class ContainerTest extends TestCase
{

    public function testGetSimpleClass(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Foo.php');
        $container = new Container();
        $foo = $container->get(Foo::class);
        static::assertInstanceOf(Foo::class, $foo);
    }

    public function testGetClassWithClassAndVariableOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Bar.php');
        $container = new Container();
        $bar = $container->get(Bar::class);
        static::assertInstanceOf(Bar::class, $bar);
    }

    public function testGetClassWithClassAndVariableOnConstructGiven(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Bar.php');
        $container = new Container();
        $container->add(Bar::class, null, [new Foo(), 'test']);
        $bar = $container->get(Bar::class);
        static::assertInstanceOf(Bar::class, $bar);
    }

    public function testGetClassWithClassAndNeededVariableNotGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    public function testGetClassWithInterfaceAndNeededVariableNotGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(FooInterface::class, Foo::class);
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    public function testGetWithoutNeededInterfaceWithNamedVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $this->expectException(NotFoundException::class);
        $container->get(Origin::class);
    }

    public function testGetWithNeededInterfaceWithNamedVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        static::assertInstanceOf(Origin::class, $origin);
    }

    public function testGetWithNeededInterfaceWithVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->add(Origin::class, null, ['test' => 'test']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        static::assertInstanceOf(Origin::class, $origin);
    }

    public function testGetWithNeededInterfaceWithReferenceVariableGivenOnConstruct(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Origin.php');
        $container = new Container();
        $container->setParameter('test', 'its ok');
        $container->add(Origin::class, null, ['test' => '%test%']);
        $container->add(FooInterface::class, Foo::class);
        $origin = $container->get(Origin::class);
        static::assertEquals('its ok', $origin->getTest());
    }

    public function testGetFalseClass(): void
    {
        $container = new Container();
        $this->expectException(NotFoundException::class);
        $container->get(Fooo::class);
    }

    public function testHasFalseClass(): void
    {
        $container = new Container();
        $has = $container->has(Fooo::class);
        self::assertEquals(null, $has);
    }

    public function testControllerFunction(): void
    {
        $container = new Container();
        $return = $container->getControllerFunc(ControllerFake::class, 'fake');
        self::assertEquals('fake function in controller', $return);
    }

    public function testControllerWithConstruct(): void
    {
        $container = new Container();
        $return = $container->getControllerFunc(ControllerWithConstructFake::class, 'fake');
        self::assertEquals('fake function in controller', $return);
    }

    public function testControllerWithConstructAndContainerUpdatedIntoIt(): void
    {
        $container = new Container();
        $container->getControllerFunc(ControllerWithConstructFake::class, 'fake');
        self::assertTrue($container->has(Foo::class));
    }

    public function testAddDefinitions(): void
    {
        $container = new Container([new Definition(FooInterface::class, Foo::class)]);
        $foo = $container->get(FooInterface::class);
        self::assertInstanceOf(Foo::class, $foo);
    }

    public function testSetGetParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', 'value');
        self::assertEquals('value', $container->getParameter('test'));
    }

    public function testSetHasParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', 'value');
        self::assertTrue($container->hasParameter('test'));
    }

    public function testGetNotFoundParameter(): void
    {
        $this->expectExceptionMessage('Class ParameterCollector, function get. The "test" parameter was not found.');
        $container = new Container();
        $container->getParameter('test');
    }

    public function testParameterIntoParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', '%othervalue%');
        $container->setParameter('othervalue', 'test an other value');
        self::assertEquals('test an other value', $container->getParameter('test'));
    }

    public function testParametersIntoParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', '%oth.er-val_ue%.another-value:%value2%');
        $container->setParameter('oth.er-val_ue', 'test an other value');
        $container->setParameter('value2', 'a good new value two');
        self::assertEquals('test an other value.another-value:a good new value two', $container->getParameter('test'));
    }

    public function testParametersIntoArrayParameter(): void
    {
        $container = new Container();
        $container->setParameter('test', ['%oth.er-val_ue%.another-value:%value2%', 'a simple replacement:%value2%']);
        $container->setParameter('oth.er-val_ue', 'test an other value');
        $container->setParameter('value2', 'a good new value two');
        self::assertEquals(
            ['test an other value.another-value:a good new value two', 'a simple replacement:a good new value two'],
            $container->getParameter('test')
        );
    }

    public function testBadLoader(): void
    {
        $this->expectException('RuntimeException');
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(Loader\BadLoader::class);
    }

    public function testLoaderYaml(): void
    {
        $container = new Container();
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(Loader\YamlLoader::class)
            ->loadParameters($container);
        self::assertEquals(3, $container->getParameter('config1.one'));
    }

    public function testLoaderYamlArray(): void
    {
        $container = new Container();
        $loader = new Loader();
        $loader
            ->setFileList(PathManipulation::fileList(__DIR__ . DIRECTORY_SEPARATOR . 'Files'))
            ->setLoader(Loader\YamlLoader::class)
            ->loadParameters($container);
        self::assertEquals([0 => 'onevaluelist', 1 => 'secondvaluelist', 2 => 'thirdvaluelist'], $container->getParameter('config3'));
    }

    public function testSetSimpleClass(): void
    {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'FakeClass' . DIRECTORY_SEPARATOR . 'Foo.php');
        $container = new Container();
        $container->set(Foo::class, new Foo());
        static::assertInstanceOf(Foo::class, $container->get(Foo::class));
    }

    public function testGetInstance(): void
    {
        static::assertInstanceOf(Container::class, (new Container())->getInstance());
    }

    public function testHasEnvParameter(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', 'env(test)');
        self::assertTrue($container->hasParameter('test_env'));
    }

    public function testGetEnvParameter(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', 'env(test)');
        self::assertEquals('hello world', $container->getParameter('test_env'));
    }

    public function testGetEnvParameterInArray(): void
    {
        putenv('test=hello world');
        $container = new Container();
        $container->setParameter('test_env', ['key' => 'env(test)']);
        self::assertEquals(['key' => 'hello world'], $container->getParameter('test_env'));
    }

}