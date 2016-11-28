<?php
namespace Minphp\Container\Exception;

use InvalidArgumentException;
use Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
