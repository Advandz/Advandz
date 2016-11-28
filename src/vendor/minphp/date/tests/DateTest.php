<?php
namespace Minphp\Date\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Date\Date;

/**
 * @coversDefaultClass \Minphp\Date\Date
 */
class DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('\Minphp\Date\Date', new Date());
    }
}
