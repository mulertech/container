<?php

namespace MulerTech\Container;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * @package MulerTech\Container
 * @author Sébastien Muler
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
