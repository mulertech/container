<?php

namespace MulerTech\Container\Loader;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlLoader
 * @package MulerTech\Container\Loader
 * @author Sébastien Muler
 */
class YamlLoader implements LoaderInterface
{
    private const array EXTENSION = ['yml', 'yaml'];

    /**
     * @param array<int, string> $fileList
     * @return array<int, mixed>
     */
    public static function load(array $fileList): array
    {
        $fileLoaded = [];

        array_map(static function (string $filename) use (&$fileLoaded) {
            if (in_array(pathinfo($filename, PATHINFO_EXTENSION), self::EXTENSION, true)) {
                $fileLoaded[] = self::loadFile($filename);
            }
        }, $fileList);

        return array_merge(...$fileLoaded);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    private static function loadFile(string $filename): mixed
    {
        return Yaml::parseFile($filename);
    }
}
