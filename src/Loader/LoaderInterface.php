<?php

namespace MulerTech\Container\Loader;

/**
 * Interface LoaderInterface.
 *
 * @author Sébastien Muler
 */
interface LoaderInterface
{
    /**
     * @param array<int, string> $fileList
     *
     * @return array<int, mixed>
     */
    public static function load(array $fileList): array;
}
