<?php

class EncryptionTest extends PHPUnit_Framework_TestCase
{
    private $Encryption;
    private $string;
    private $encrypted;

    public function setUp()
    {
        $this->Encryption = new Advandz\Component\Encryption();

        $key = $this->Encryption->generateKey();
        $this->Encryption->setKey($key);

        $this->string = 'test';
    }

    /**
     * @covers Encryption::encrypt
     */
    public function testEncrypt()
    {
        $this->encrypted = $this->Encryption->encrypt($this->string);
        $this->assertTrue(is_string($this->encrypted));
    }

    /**
     * @covers Encryption::decrypt
     */
    public function testDecrypt()
    {
        $this->assertSame($this->string, $this->Encryption->decrypt($this->encrypted));
    }

    /**
     * @covers Encryption::generateKey
     */
    public function testGenerateKey()
    {
        $this->assertTrue(is_string($this->Encryption->generateKey()));
    }
}
