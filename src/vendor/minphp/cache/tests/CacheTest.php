<?php
namespace Minphp\Cache\Tests;

use Minphp\Cache\Cache;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \Minphp\Cache\Cache
 */
class CacheTest extends PHPUnit_Framework_TestCase
{
    private $cache_dir;
    private $cache;

    protected function setUp()
    {
        $this->cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "Fixtures" . DIRECTORY_SEPARATOR;
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
        $this->cache = new Cache($this->cache_dir);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->cache->clear();
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        file_put_contents($this->cache_dir . "testfile", "CacheTest::testClear");
        $this->assertFileExists($this->cache_dir . "testfile");

        $this->cache->clear("bad/sub/path/");
        $this->assertFileExists($this->cache_dir . "testfile");

        $this->cache->clear();
        $this->assertFileNotExists($this->cache_dir . "testfile");

        mkdir($this->cache_dir . "sub/path", 0777, true);
        file_put_contents($this->cache_dir . "sub/path/testfile", "CacheTest::testEmptyCache");
        $this->assertFileExists($this->cache_dir . "sub/path/testfile");
        $this->cache->clear("sub/path/");
        $this->assertFileNotExists($this->cache_dir . "sub/path/testfile");
        rmdir($this->cache_dir . "sub/path");
    }

    /**
     * @covers ::remove
     * @covers ::cacheName
     * @uses \Minphp\Cache\Cache::fetch
     * @uses \Minphp\Cache\Cache::write
     */
    public function testRemove()
    {
        $cache_name = "testfile";
        $cache_contents = "CacheTest::testRemove";
        $this->assertFalse($this->cache->remove("bad_file_name"));

        $this->cache->write($cache_name, $cache_contents, 10);
        $this->assertEquals($cache_contents, $this->cache->fetch($cache_name));

        $this->assertTrue($this->cache->remove($cache_name));

        $this->assertFalse($this->cache->fetch($cache_name));
    }

    /**
     * @covers ::write
     * @covers ::cacheName
     * @uses \Minphp\Cache\Cache::fetch
     * @uses \Minphp\Cache\Cache::remove
     */
    public function testWriteCache()
    {
        $cache_name = "testfile";
        $cache_contents = "CacheTest::testWrite";

        $this->cache->write($cache_name, $cache_contents, -1);
        $this->assertFalse($this->cache->fetch($cache_name));

        $this->assertTrue($this->cache->remove($cache_name));

        $this->cache->write($cache_name, $cache_contents, 1);
        $this->assertEquals($cache_contents, $this->cache->fetch($cache_name));

        $this->assertTrue($this->cache->remove($cache_name));
    }

    /**
     * @covers ::fetch
     * @covers ::cacheName
     * @uses \Minphp\Cache\Cache::write
     * @uses \Minphp\Cache\Cache::remove
     */
    public function testFetchCache()
    {
        $cache_name = "testfile";
        $cache_contents = "CacheTest::testFetch";

        $this->cache->write($cache_name, $cache_contents, -1);
        $this->assertFalse($this->cache->fetch($cache_name));

        $this->assertTrue($this->cache->remove($cache_name));

        $this->cache->write($cache_name, $cache_contents, 1);
        $this->assertEquals($cache_contents, $this->cache->fetch($cache_name));

        $this->assertTrue($this->cache->remove($cache_name));
    }
}
