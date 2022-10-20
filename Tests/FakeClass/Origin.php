<?php


namespace mtphp\Container\Tests\FakeClass;

use mtphp\Container\Tests\FakeClass\Foo;

class Origin
{

    private $foo;
    private $test;
    private $parameter;

    public function __construct(FooInterface $foo, string $test, WithParameter $withParameter = null)
    {
        $this->foo = $foo;
        $this->test = $test;
        $this->parameter = $withParameter;
    }

    public function getFoo()
    {
        return $this->foo;
    }
    public function getTest()
    {
        return $this->test;
    }
    public function getParameter()
    {
        return $this->parameter;
    }
}