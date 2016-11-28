<?php
namespace Minphp\Container\Tests\Exception;

use PHPUnit_Framework_TestCase;
use Minphp\Container\Exception\NotFoundException;

/**
 * @coversDefaultClass \Minphp\Container\Exception\NotFoundException
 */
class NotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $this->assertInstanceOf('\Interop\Container\Exception\NotFoundException', new NotFoundException());
    }
}
