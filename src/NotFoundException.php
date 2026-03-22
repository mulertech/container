<?php

namespace MulerTech\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException.
 *
 * @author Sébastien Muler
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
