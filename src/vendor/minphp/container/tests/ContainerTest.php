<?php
namespace Minphp\Container\Tests;

use Minphp\Container\Container;
use PHPUnit_Framework_TestCase;
use SplQueue;

/**
 * @coversDefaultClass \Minphp\Container\Container
 */
class ContainerTest extends PHPUnit_Framework_TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    /**
     * @covers ::get
     * @uses \Minphp\Container\Container::set
     */
    public function testGet()
    {
        $this->container->set('id', 'value');
        $this->assertEquals('value', $this->container->get('id'));
    }

    /**
     * @covers ::get
     * @expectedException \Minphp\Container\Exception\NotFoundException
     */
    public function testGetException()
    {
        $this->assertEquals('value', $this->container->get('non-existent-id'));
    }

    /**
     * @covers ::has
     * @uses \Minphp\Container\Container::set
     */
    public function testHas()
    {
        $this->container->set('id', 'value');
        $this->assertTrue($this->container->has('id'));
        $this->assertFalse($this->container->has('non-existent-id'));
    }

    /**
     * @covers ::set
     * @uses \Minphp\Container\Container::get
     */
    public function testSet()
    {
        $this->container->set(
            'queue',
            function ($c) {
                return new SplQueue();
            }
        );
        $queue = $this->container->get('queue');
        $queue->enqueue('hello');

        $this->assertEquals('hello', $this->container->get('queue')->dequeue());
    }

    /**
     * @covers ::remove
     * @uses \Minphp\Container\Container::set
     * @uses \Minphp\Container\Container::has
     */
    public function testRemove()
    {
        $this->container->set('id', 'value');
        $this->assertTrue($this->container->has('id'));

        $this->container->remove('id');
        $this->assertFalse($this->container->has('id'));
    }
}
