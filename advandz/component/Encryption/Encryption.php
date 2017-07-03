<?php
/**
 * Provides an easy way of encrypting data with a key or password.
 *
 * @package Advandz
 * @subpackage Advandz.components.encryption
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

class Encryption
{
    /**
     * @var string The encryption key.
     */
    private $key = '';

    /**
     * @var string The algorithm used for encryption.
     */
    private $algorithm = 'AES-128-CBC';

    /**
     * Constructs a new Encryption object.
     */
    public function __construct()
    {
        // Fetch default encryption key
        $key = \Configure::get('Encryption.key');

        if (!empty($key)) {
            $this->setKey($key);
        }
    }

    /**
     * Encrypt a value using OpenSSL and the selected algorithm.
     *
     * @param  mixed     $data The data to be encrypted, A string, an array or an object
     * @throws Exception When the data cannot be encrypted
     * @return string    The encrypted data
     */
    public function encrypt($data)
    {
        // Check if a valid key is set
        if (empty($this->key)) {
            throw new \Exception('An encryption key isn\'t provided');
        }

        // Generate a random IV
        $iv = hex2bin($this->generateKey(16));

        // Serialize the data
        if (!is_string($data)) {
            if (is_array($data) || is_object($data)) {
                $serialize = true;
                $data      = serialize($data);
            } else {
                throw new \Exception('The data cannot be encrypted, Data passed must be an instance of string, object or array');
            }
        } else {
            $serialize = false;
        }

        // Encrypt the value using OpenSSL
        $data = openssl_encrypt($data, $this->algorithm, hex2bin($this->key), 0, $iv);

        if ($data === false) {
            throw new \Exception('The data cannot be encrypted');
        }

        // Calculate a MAC for the encrypted data
        $mac = hash_hmac('sha256', base64_encode($iv) . $data, $this->key);

        // Build an array with the encrypted data
        $result = json_encode([
            'iv'        => base64_encode($iv),
            'mac'       => $mac,
            'data'      => $data,
            'serialize' => $serialize
        ]);

        if (!is_string($result)) {
            throw new \Exception('The data cannot be encrypted, The JSON parser returns null or an error');
        }

        return base64_encode($result);
    }

    /**
     * Decrypt a value using OpenSSL and the selected algorithm.
     *
     * @param  string    $data The data to be decrypted, A string, an array or an object
     * @throws Exception When the data cannot be decrypted
     * @return mixed     The decrypted data, A string, an array or an object
     */
    public function decrypt($data)
    {
        // Check if a valid key is set
        if (empty($this->key)) {
            throw new \Exception('An encryption key isn\'t provided');
        }

        // Decode the encrypted data
        $data = json_decode(base64_decode($data), true);

        // Check if the encrypted data is valid
        if (is_array($data) && isset($data['iv'], $data['mac'], $data['data'], $data['serialize'])) {
            // Check if the HMAC is valid
            $mac = hash_hmac('sha256', $data['iv'] . $data['data'], $this->key);

            if ($mac === $data['mac']) {
                $decrypted = openssl_decrypt($data['data'], $this->algorithm, hex2bin($this->key), 0, base64_decode($data['iv']));

                if ($decrypted === false) {
                    throw new \Exception('The data cannot be decrypted');
                }

                // Unserialize the data
                if ($data['serialize']) {
                    $decrypted = unserialize($decrypted);
                }

                return $decrypted;
            } else {
                throw new \Exception('The data cannot be decrypted, Invalid HMAC');
            }
        } else {
            throw new \Exception('The data cannot be decrypted, The given data is invalid');
        }
    }

    /**
     * Set the key to be used in the encryption and decryption process.
     *
     * @param  string    $key       A binary pseudo-random key hexadecimally encoded
     * @param  mixed     $algorithm
     * @throws Exception When the algorithm or key is invalid
     */
    public function setAlgorithm($algorithm)
    {
        if ($this->supportedKey($this->key, $algorithm)) {
            $this->algorithm = $algorithm;
        } else {
            throw new \Exception('The only supported ciphers algorithms are AES-128-CBC and AES-256-CBC with the correct key lengths');
        }
    }

    /**
     * Set the key to be used in the encryption and decryption process.
     *
     * @param  string    $key A binary pseudo-random key hexadecimally encoded
     * @throws Exception When the algorithm or key is invalid
     */
    public function setKey($key)
    {
        if ($this->supportedKey($key, $this->algorithm)) {
            $this->key = $key;
        } else {
            throw new \Exception('The only supported ciphers algorithms are AES-128-CBC and AES-256-CBC with the correct key lengths');
        }
    }

    /**
     * Generates cryptographically secure pseudo-random key.
     *
     * @param  int    $length Hashing algorithm
     * @return string A binary pseudo-random key hexadecimally encoded
     */
    public function generateKey($length = 16)
    {
        if (function_exists('random_bytes')) {
            // First we will try to use the default random bytes generator
            $bytes = random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            // If the first option is not available, We will try to use the
            // OpenSSL random bytes generator
            $bytes = openssl_random_pseudo_bytes($length);
        } elseif (function_exists('mcrypt_create_iv')) {
            // If OpenSSL is not available, We will try to generate a IV
            $bytes = mcrypt_create_iv($length);
        }

        return bin2hex($bytes);
    }

    /**
     * Determine if the given key and cipher algorithm combination is valid.
     *
     * @param  string $key       A binary pseudo-random key hexadecimally encoded
     * @param  string $algorithm The algorithm used for encryption
     * @return bool   True if the key is valid for the algorithm
     */
    public function supportedKey($key, $algorithm)
    {
        $key    = hex2bin($key);
        $length = mb_strlen($key, '8bit');

        return ($algorithm === 'AES-128-CBC' && $length === 16) || ($algorithm === 'AES-256-CBC' && $length === 32);
    }
}
