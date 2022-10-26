<?php


namespace MulerTech\Container\Tests\FakeClass;

use Psr\Container\ContainerInterface;

class ControllerWithConstructFake
{

    public function __construct(ContainerInterface $container)
    {
        $container->add(Foo::class);
    }

    public function fake()
    {
        return 'fake function in controller';
    }
}