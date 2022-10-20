<?php


namespace mtphp\Container\Loader;


interface LoaderInterface
{

    public static function load(array $fileList): array;
}