<?php
namespace Minphp\Configure\Tests\Reader;

use Minphp\Configure\Reader\PhpReader;
use PHPUnit_Framework_TestCase;
use SplTempFileObject;

/**
 * @coversDefaultClass \Minphp\Configure\Reader\PhpReader
 */
class PhpReaderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("\Minphp\Configure\Reader\ReaderInterface", new PhpReader(new SplTempFileObject()));
    }

    protected function getFixturePath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . "Fixtures" . DIRECTORY_SEPARATOR;
    }

    /**
     * @covers ::getIterator
     * @uses \Minphp\Configure\Reader\PhpReader
     */
    public function testGetIterator()
    {
        $file = $this->getFileMock($this->getFixturePath() . "Config.php");
        $reader = new PhpReader($file);

        $result = $reader->getIterator($file);
        $this->assertInstanceOf('\ArrayIterator', $result);
        $this->assertEquals("value", $result['key']);
    }

    /**
     * @covers ::getIterator
     * @uses \Minphp\Configure\Reader\PhpReader
     * @expectedException \Minphp\Configure\Reader\Exception\ReaderParseException
     */
    public function testGetIteratorException()
    {
        $file = $this->getFileMock(null);
        $reader = new PhpReader($file);
        $result = $reader->getIterator();
    }

    protected function getFileMock($filename)
    {
        $file = $this->getMockBuilder('\SplFileObject')
            ->setConstructorArgs(array("php://temp"))
            ->setMethods(array('isFile', 'getPathname'))
            ->getMock();

        $file->method('isFile')
            ->will($this->returnValue($filename != null));

        $file->method('getPathname')
            ->will($this->returnValue($filename));


        return $file;
    }
}
