<?php
class HttpTest extends PHPUnit_Framework_TestCase
{
    
    private $Http;
    
    public function setUp()
    {
        $this->Http = new Advandz\Component\Http();
    }
    
    /**
     * @covers Input::execute
     */
    public function testExecute()
    {
        $this->assertTrue($this->Http->server('advandz.com')->method('GET')->execute());
    }
}
