<?php

namespace MulerTech\Container\Loader;

/**
 * Interface LoaderInterface
 * @package MulerTech\Container\Loader
 * @author Sébastien Muler
 */
interface LoaderInterface
{

    public static function load(array $fileList): array;
}