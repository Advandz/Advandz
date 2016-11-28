<?php
namespace Minphp\Configure\Tests\Reader;

use PHPUnit_Framework_TestCase;
use Minphp\Configure\Reader\ReaderInterface;
use ArrayIterator;

/**
 * @coversDefaultClass \Minphp\Configure\Reader\ReaderInterface
 */
class ReaderInterfaceTest extends PHPUnit_Framework_TestCase implements ReaderInterface
{

    public function getIterator()
    {
        return new ArrayIterator();
    }

    /**
     * @covers ::getIterator
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf("\ArrayIterator", $this->getIterator());
    }
}
