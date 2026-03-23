<?php

namespace MulerTech\Container\Loader;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlLoader.
 *
 * @author Sébastien Muler
 */
class YamlLoader implements LoaderInterface
{
    private const array EXTENSION = ['yml', 'yaml'];

    /**
     * @param array<int, string> $fileList
     *
     * @return array<int|string, mixed>
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
     * @return array<int|string, mixed>
     */
    private static function loadFile(string $filename): array
    {
        return (array) Yaml::parseFile($filename);
    }
}
