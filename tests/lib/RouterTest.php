<?php

class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Router::route
     * @dataProvider routeProvider
     * @expectedException Exception
     */
    public function testRoute($orig_uri, $mapped_uri)
    {
        Router::route($orig_uri, $mapped_uri);
    }

    /**
     * Data provider for RouterTest::testRoute.
     */
    public function routeProvider()
    {
        return [
            ['main/', ''],
            ['', '.*'],
        ];
    }

    /**
     * @covers Router::match
     * @covers Router::route
     */
    public function testMatch()
    {
        $uri = 'a/b/c';

        // No match
        $this->assertSame($uri, Router::match($uri));

        // Match
        Router::route($uri, '[a-z]/[a-z]/[a-z]');
        $this->assertSame('[a-z]/[a-z]/[a-z]', Router::match($uri));
    }

    /**
     * @covers Router::escape
     */
    public function testEscape()
    {
        $this->assertSame("\/a\/b\/c", Router::escape('/a/b/c'));
        $this->assertSame("\/a\/b\/c\/", Router::escape('/a/b/c/'));
    }

    /**
     * @covers Router::unescape
     */
    public function testUnescape()
    {
        $this->assertSame('/a/b/c', Router::unescape("\/a\/b\/c"));
        $this->assertSame('/a/b/c/', Router::unescape("\/a\/b\/c\/"));
    }

    /**
     * @covers Router::makeURI
     */
    public function testMakeURI()
    {
        $this->assertSame('/a/b/c/', Router::makeURI('/a/b/c/'));
        $this->assertSame('/a/b/c/', Router::makeURI('\\a\\b\\c\\'));
    }

    /**
     * @covers Router::parseURI
     */
    public function testParseURI()
    {
        $this->assertSame(['a'], Router::parseURI('a'));
        $this->assertSame(['a', 'b', 'c', '', '?w=x&y=z'], Router::parseURI('a/b/c/?w=x&y=z'));
    }

    /**
     * @covers Router::filterURI
     */
    public function testFilterURI()
    {
        $this->assertNotContains(WEBDIR, Router::filterURI(WEBDIR));
    }

    /**
     * @covers Router::isCallable
     */
    public function testIsCallable()
    {
        $controller = $this->getMockBuilder('Controller')
            ->getMock();

        $this->assertTrue(Router::isCallable($controller, 'index'));
        $this->assertFalse(Router::isCallable($controller, 'preAction'));
        $this->assertFalse(Router::isCallable($controller, 'nonexistentMethod'));
        $this->assertFalse(Router::isCallable(null, null));
    }

    /**
     * @covers Router::routesTo
     * @dataProvider routesToProvider
     */
    public function testRoutesTo($uri)
    {
        $result = Router::routesTo($uri);

        $this->assertArrayHasKey('plugin', $result);
        $this->assertArrayHasKey('controller', $result);
        $this->assertArrayHasKey('action', $result);
        $this->assertArrayHasKey('get', $result);
        $this->assertArrayHasKey('uri', $result);
        $this->assertArrayHasKey('uri_str', $result);
        $this->assertSame(rtrim($uri, '/').'/', $result['uri_str']);
    }

    /**
     * Data provider for testRoutesTo.
     */
    public function routesToProvider()
    {
        return [
            ['controller/action/get1/get2'],
            ['controller/action/key1:value1/key2:value2'],
        ];
    }
}
