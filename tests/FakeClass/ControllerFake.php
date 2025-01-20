<?php

namespace MulerTech\Container\Tests\FakeClass;

class ControllerFake
{
    public function fake()
    {
        return 'fake function in controller';
    }

    public function dontWork(string $dontGiveThis)
    {
        return 'this dont work';
    }
}