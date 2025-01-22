<?php

namespace MulerTech\Container\Loader;

/**
 * Interface LoaderInterface
 * @package MulerTech\Container\Loader
 * @author Sébastien Muler
 */
interface LoaderInterface
{
    /**
     * @param array<int, string> $fileList
     * @return array<int, mixed>
     */
    public static function load(array $fileList): array;
}
