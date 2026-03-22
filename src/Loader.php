<?php

namespace MulerTech\Container;

use MulerTech\Container\Loader\LoaderInterface;
use MulerTech\Container\Loader\LoaderNotFoundException;

/**
 * Class Loader.
 *
 * @author Sébastien Muler
 *
 * @template T of object
 */
class Loader
{
    /**
     * The load function name into the loaders.
     */
    private const string LOAD_FUNCTION = 'load';

    /**
     * @var array<int, class-string>
     */
    private array $loaders = [];
    /**
     * @var array<int, string>
     */
    private array $fileList;
    private Container $container;

    /**
     * @param class-string $loader
     *
     * @return Loader<LoaderInterface>
     */
    public function setLoader(string $loader): Loader
    {
        if (!is_callable([$loader, self::LOAD_FUNCTION])) {
            throw new LoaderNotFoundException(sprintf('Class Loader, function setLoader. The "%s" loader doesnt exists or don\'t have the load function.', $loader));
        }

        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * @param array<int, string> $fileList
     *
     * @return Loader<LoaderInterface>
     */
    public function setFileList(array $fileList): Loader
    {
        $this->fileList = $fileList;

        return $this;
    }

    public function loadParameters(Container $container): void
    {
        $this->container = $container;
        if (!empty($this->fileList)) {
            foreach ($this->loaders as $loader) {
                /* @var class-string<LoaderInterface> $loader */
                $this->extractParameters($loader::load($this->fileList));
            }
        }
    }

    /**
     * Extract parameters with unlimited levels from this :
     * ['firstlevel' => ['secondlevel' => ['thirdlevel' => 'some values']]]
     * to this parameter :
     * 'firstlevel.secondlevel'
     * with value :
     * ['thirdlevel' => 'some values']
     *
     * @param array<int|string, mixed> $filesLoaded
     */
    private function extractParameters(array $filesLoaded, ?string $prefix = null): void
    {
        foreach ($filesLoaded as $key => $item) {
            if (is_numeric($key)) {
                continue;
            }

            $key = (is_null($prefix)) ? $key : $prefix.'.'.$key;
            $this->container->setParameter($key, $item);
            if (is_array($item)) {
                $this->extractParameters($item, $key);
            }
        }
    }
}
