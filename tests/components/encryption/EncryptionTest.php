<?php

class EncryptionTest extends PHPUnit_Framework_TestCase
{
    private $Encryption;
    private $string;
    private $encrypted;

    public function setUp()
    {
        $this->Encryption = new Advandz\Component\Encryption();
    }

    /**
     * @covers Encryption::encrypt
     */
    public function testEncrypt()
    {
        $key = $this->Encryption->generateKey();

        $this->Encryption->setKey($key);

        $string = 'test';

        $this->assertTrue(is_string($this->Encryption->encrypt($string)));
    }

    /**
     * @covers Encryption::decrypt
     */
    public function testDecrypt()
    {
        $key = '321626b697b7a5936b8e720dfcdbbdda';
        $expected = 'test';
        $encrypted = 'eyJpdiI6Im9CTnVOTEZwZjNXSEs1dVlRMUhuYUE9PSIsIm1hYyI6ImJiZTAwZmM1ZjhlMDI5ZmZmNWQzNTBlN2ZhNzc0NmRjZGE2ZjQzMWNmMGI0ZTExNzg4NjQ4ZGFkZjA3YWRiNmMiLCJkYXRhIjoiYTlLaVNMKzNVc0FOXC9SM0dqWHVmdHc9PSIsInNlcmlhbGl6ZSI6ZmFsc2V9';

        $this->Encryption->setKey($key);
        
        $this->assertSame($expected, $this->Encryption->decrypt($encrypted));
    }

    /**
     * @covers Encryption::generateKey
     */
    public function testGenerateKey()
    {
        $this->assertTrue(is_string($this->Encryption->generateKey()));
    }
}
