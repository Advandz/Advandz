<?php
namespace Minphp\Configure\Tests\Reader;

use PHPUnit_Framework_TestCase;
use Minphp\Configure\Reader\JsonReader;
use SplTempFileObject;

/**
 * @coversDefaultClass \Minphp\Configure\Reader\JsonReader
 */
class JsonReaderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("\Minphp\Configure\Reader\ReaderInterface", new JsonReader(new SplTempFileObject()));
    }

    /**
     * @covers ::getIterator
     * @uses \Minphp\Configure\Reader\JsonReader
     */
    public function testGetIterator()
    {
        $file = $this->getFileMock(json_encode(array('key' => "value")));
        $reader = new JsonReader($file);

        $result = $reader->getIterator();
        $this->assertInstanceOf('\ArrayIterator', $result);
        $this->assertEquals("value", $result['key']);
    }

    /**
     * @covers ::getIterator
     * @uses \Minphp\Configure\Reader\JsonReader
     * @expectedException \Minphp\Configure\Reader\Exception\ReaderParseException
     */
    public function testGetIteratorException()
    {
        $file = $this->getFileMock("{malformed:}}");
        $reader = new JsonReader($file);
        $reader->getIterator();
    }

    protected function getFileMock($data)
    {
        $file = $this->getMockBuilder('\SplFileObject')
            ->setConstructorArgs(array("php://temp"))
            ->setMethods(array('eof', 'fgets'))
            ->getMock();

        $file->method('fgets')
            ->will($this->returnValue($data));

        $file->method('eof')
            ->will($this->onConsecutiveCalls(false, true));


        return $file;
    }
}
