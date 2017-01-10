<?php

class HttpTest extends PHPUnit_Framework_TestCase
{
    private $Http;

    public function setUp()
    {
        $this->Http = new Advandz\Component\Http();
    }

    /**
     * @covers Http::server
     */
    public function testServer()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->server('advandz.com'));
    }

    /**
     * @covers Http::uri
     */
    public function testUri()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->uri('/'));
    }

    /**
     * @covers Http::port
     */
    public function testPort()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->port(80));
    }

    /**
     * @covers Http::useSsl
     */
    public function testUseSsl()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->useSsl());
    }

    /**
     * @covers Http::headers
     */
    public function testHeaders()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->headers(['Cache-Control: no-cache']));
    }

    /**
     * @covers Http::auth
     */
    public function testAuth()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->auth(['user', 'pass']));
    }

    /**
     * @covers Http::method
     */
    public function testMethod()
    {
        $this->assertInstanceOf('Advandz\\Component\\Http', $this->Http->method('GET'));
    }
}
