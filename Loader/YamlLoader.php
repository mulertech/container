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

    private const EXTENSION = 'yaml';

    /**
     * @param array $fileList
     * @return array
     */
    public static function load(array $fileList): array
    {
        $fileLoaded = [];
        foreach ($fileList as $filename) {
            if (pathinfo($filename, PATHINFO_EXTENSION) === self::EXTENSION) {
                $fileLoaded[] = self::loadFile($filename);
            }
        }
        return array_merge(...$fileLoaded);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    private static function loadFile(string $filename)
    {
        return Yaml::parseFile($filename);
    }
}