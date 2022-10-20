<?php


namespace mtphp\Container;


use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Class Loader
 * @package mtphp\Container
 * @author Sébastien Muler
 */
class Loader
{

    /**
     * The load function name into the loaders.
     */
    private const LOAD_FUNCTION = 'load';

    /**
     * @var string[] $loaders
     */
    private $loaders = [];
    /**
     * @var array $fileList
     */
    private $fileList;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param string $loader
     * @return Loader
     */
    public function setLoader(string $loader): Loader
    {
        if (!is_callable([$loader, self::LOAD_FUNCTION])) {
            throw new RuntimeException(
                sprintf('Class Loader, function setLoader. The "%s" loader don\'t have the load function.', $loader)
            );
        }
        $this->loaders[] = $loader;
        return $this;
    }

    /**
     * @param array $fileList
     * @return Loader
     */
    public function setFileList(array $fileList): Loader
    {
        $this->fileList = $fileList;
        return $this;
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function loadParameters(ContainerInterface $container): void
    {
        $this->container = $container;
        if (!empty($this->fileList)) {
            foreach ($this->loaders as $loader) {
                $this->extractParameters(call_user_func([$loader, 'load'], $this->fileList));
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
     * @param array $filesLoaded
     * @param string|null $prefix
     */
    private function extractParameters(array $filesLoaded, string $prefix = null): void
    {
        foreach ($filesLoaded as $key => $item) {
            if (is_numeric($key)) {
                continue;
            }
            $key = (is_null($prefix)) ? $key : $prefix . '.' . $key;
            $this->container->setParameter($key, $item);
            if (is_array($item)) {
                $this->extractParameters($item, $key);
            }
        }
    }
//    private function extractParameters(array $filesLoaded, string $prefix = null): void
//    {
//        foreach ($filesLoaded as $key => $item) {
//            if (is_array($item)) {
//                $this->extractParameters($item, $key);
//            }
//            if (!is_numeric($key)) {
//                $key = (is_null($prefix)) ? $key : $prefix . '.' . $key;
//                $this->container->setParameter($key, $item);
//            }
//        }
//    }
}