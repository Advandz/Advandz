<?php
namespace Minphp\Configure\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Configure\Configure;

/**
 * @coversDefaultClass \Minphp\Configure\Configure
 */
class ConfigureTest extends PHPUnit_Framework_TestCase
{

    private $Configure;

    public function setUp()
    {
        $this->Configure = new Configure();
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf("\Minphp\Configure\Configure", $this->Configure);
    }

    /**
     * Load the configuration
     *
     * @param \Minphp\Configure\Reader\ReaderInterface $reader
     */
    protected function loadConfig($reader)
    {
        $this->Configure->load($reader);
    }

    /**
     * @covers ::set
     * @covers ::get
     * @uses \Minphp\Configure\Configure
     * @dataProvider keyProvider
     */
    public function testSetGet($data)
    {
        $keys = array_keys($data);
        $this->loadConfig($this->getReaderMock($data));

        foreach ($keys as $key) {
            $before = $this->Configure->get($key);
            $after = "hello world";
            $this->Configure->set($key, $after);
            $this->assertEquals($after, $this->Configure->get($key));
        }

        $this->assertNull($this->Configure->get("key-not-in-the-set"));
    }

    /**
     * @covers ::remove
     * @covers ::exists
     * @uses \Minphp\Configure\Configure
     * @dataProvider keyProvider
     */
    public function testRemove($data)
    {
        $keys = array_keys($data);
        $this->loadConfig($this->getReaderMock($data));

        foreach ($keys as $key) {
            $this->assertTrue($this->Configure->exists($key));
            $this->Configure->remove($key);
            $this->assertFalse($this->Configure->exists($key));
        }

        $this->Configure->remove("key-not-in-the-set");
    }

    /**
     * Data provider for testFree, testGet, testSet
     *
     * @return array
     */
    public function keyProvider()
    {
        return $this->genericProvider(true);
    }

    /**
     * @covers ::load
     * @uses \Minphp\Configure\Configure
     * @dataProvider loadProvider
     */
    public function testLoad($data)
    {
        $this->loadConfig($this->getReaderMock($data));
    }

    /**
     * @covers ::load
     * @uses \Minphp\Configure\Configure
     * @expectedException \UnexpectedValueException
     */
    public function testLoadUnexpectedValueException()
    {
        $reader = $this->getMockBuilder('\Minphp\Configure\Reader\ReaderInterface')
            ->setMethods(array('getIterator'))
            ->getMock();
        $reader->method('getIterator')
            ->will($this->returnValue(false));

        $this->Configure->load($reader);
    }


    /**
     * Data provider for testLoad
     *
     * @return array
     */
    public function loadProvider()
    {
        return $this->genericProvider();
    }

    /**
     * Mocks a Reader with the given data
     *
     * @param array $data
     */
    protected function getReaderMock($data)
    {
        $reader = $this->getMockBuilder('\Minphp\Configure\Reader\ReaderInterface')
            ->setMethods(array('getIterator'))
            ->getMock();

        $reader->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($data)));

        return $reader;
    }

    /**
     * Generic data provider for Configure
     *
     * @param boolean $with_keys
     * @return array
     */
    protected function genericProvider()
    {
        return array(
            array($this->getConfigData())
        );
    }

    /**
     * Sample config data
     *
     * @return array
     */
    protected function getConfigData()
    {
        return array(
            'key1' => "value1",
            'key2' => true,
            'key3' => array(
                "item1",
                "item2"
            ),
            'key4' => array(
                'subkey1' => 10,
                'subkey2' => null
            )
        );
    }
}
