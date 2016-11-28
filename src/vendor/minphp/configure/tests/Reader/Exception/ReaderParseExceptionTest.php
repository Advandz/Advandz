<?php
namespace Minphp\Configure\Tests\Reader\Exception;

use PHPUnit_Framework_TestCase;
use Minphp\Configure\Reader\Exception\ReaderParseException;

class ReaderParseExceptionTest extends PHPUnit_Framework_TestCase
{

    public function testInstance()
    {
        $this->assertInstanceOf('Exception', new ReaderParseException('test'));
    }
}
