<?php

namespace MulerTech\Container\Tests\FakeClass;

class Origin
{
    private FooInterface $foo;
    private string $test;
    private ?WithParameter $parameter;

    public function __construct(FooInterface $foo, string $test, ?WithParameter $withParameter = null)
    {
        $this->foo = $foo;
        $this->test = $test;
        $this->parameter = $withParameter;
    }

    public function getTest()
    {
        return $this->test;
    }
}