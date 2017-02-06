<?php

class PasswordTest extends PHPUnit_Framework_TestCase
{
    private $Password;

    public function setUp()
    {
        $this->Password = new Advandz\Component\Password();
    }

    /**
     * @covers Password::generate
     */
    public function testGenerate()
    {
        $this->assertNotEmpty($this->Password->generate());
    }

    /**
     * @covers Password::hash
     */
    public function testHash()
    {
        $password = $this->Password->generate();
        $this->assertNotEmpty($this->Password->hash($password));
    }

    /**
     * @covers Password::getHashInfo
     */
    public function testGetHashInfo()
    {
        $hash = '$2y$10$Vjn3Bui2l8og8T5UAcU2kei0UKhHOgwf/UQZzkj7NT5a2xew/5KJy';
        $this->assertTrue(is_array($this->Password->getHashInfo($hash)));
    }

    /**
     * @covers Password::needsRehash
     */
    public function testNeedsRehash()
    {
        $hash = '$2y$10$Vjn3Bui2l8og8T5UAcU2kei0UKhHOgwf/UQZzkj7NT5a2xew/5KJy';
        $this->assertTrue(!$this->Password->needsRehash($hash));
    }

    /**
     * @covers Password::verify
     */
    public function testVerify()
    {
        $password = '0cXGx-cUhhBu';
        $hash = '$2y$10$Vjn3Bui2l8og8T5UAcU2kei0UKhHOgwf/UQZzkj7NT5a2xew/5KJy';
        $this->assertTrue($this->Password->verify($password, $hash));
    }
}
