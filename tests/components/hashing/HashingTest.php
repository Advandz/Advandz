<?php

class HashingTest extends PHPUnit_Framework_TestCase
{
    private $Hashing;

    public function setUp()
    {
        $this->Hashing = new Advandz\Component\Hashing();
    }

    /**
     * @covers Hashing::hmacHash
     */
    public function testHmacHash()
    {
        $expected = '63d6baf65df6bdee8f32b332e0930669';
        $this->assertSame($expected, $this->Hashing->hmacHash('md5', 'test', 'secret'));
    }

    /**
     * @covers Hashing::hash
     */
    public function testHash()
    {
        $expected = '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';
        $this->assertSame($expected, $this->Hashing->hash('sha256', 'test'));
    }

    /**
     * @covers Hashing::listHashAlgorithms
     */
    public function testListHashAlgorithms()
    {
        $this->assertTrue(is_array($this->Hashing->listHashAlgorithms()));
    }

    /**
     * @covers Hashing::compareHash
     */
    public function testCompareHash()
    {
        $a = '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';
        $b = '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';
        $this->assertTrue($this->Hashing->compareHash($a, $b));
    }
}
