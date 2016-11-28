<?php
namespace Minphp\Xml\Tests;

use PHPUnit_Framework_TestCase;
use Minphp\Xml\Xml;

/**
 * @coversDefaultClass \Minphp\Xml\Xml
 */
class XmlTest extends PHPUnit_Framework_TestCase
{
    private $xml;

    public function setUp()
    {
        $this->xml = new Xml();
    }
    /**
     * @covers ::xmlEntities
     */
    public function testXmlEntities()
    {
        $this->assertEmpty($this->xml->xmlEntities(chr(0)));
        $this->assertEquals('&#9;&#10;&#13;', $this->xml->xmlEntities(chr(9) . chr(10) . chr(13)));
        $this->assertEquals('&amp;', $this->xml->xmlEntities('&'));
        $this->assertEquals('&lt;', $this->xml->xmlEntities('<'));
        $this->assertEquals('&gt;', $this->xml->xmlEntities('>'));
        $this->assertEquals('&quot;', $this->xml->xmlEntities('"'));
        $this->assertEquals('&apos;', $this->xml->xmlEntities('`'));
    }

    /**
     * @covers ::makeXml
     * @covers ::buildXmlSegment
     */
    public function testMakeXml()
    {
        $data = array(
            'section' => array(
                'contents' => (object)array(
                    'item1' => 1,
                    'item2' => "two",
                    'item4' => null
                ),
                'other' => array('item' => array(1, 2, 3)),
            )
        );
        $this->assertEquals(
            file_get_contents(__DIR__ . '/Fixtures/nested_array.xml'),
            $this->xml->makeXml($data)
        );
    }
}
