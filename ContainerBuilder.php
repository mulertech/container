<?php


namespace MulerTech\Container;

/**
 * Class ContainerBuilder
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class ContainerBuilder extends Container
{

    /**
     * ContainerBuilder constructor.
     * @param array $definitions
     */
    public function __construct(array $definitions = [])
    {
        parent::__construct($definitions);
        if (!$this->has(__CLASS__)) {
            $this->set(__CLASS__, $this);
        }
        if (!$this->has(Container::class)) {
            $this->set(Container::class, $this->getInstance());
        }
    }

}