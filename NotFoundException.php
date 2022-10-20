<?php


namespace mtphp\Container;


use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * @package mtphp\Container
 * @author Sébastien Muler
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{

}