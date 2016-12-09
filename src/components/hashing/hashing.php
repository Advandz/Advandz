<?php
/**
 * Provides a keyed hash using HMAC and the Hash of a specific data.
 *
 * @package Advandz
 * @subpackage Advandz.components.hashing
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
class Hashing {
    /**
     * Calculate a keyed hash using HMAC.
     *
     * @param string $algorithm Hashing algorithm.
     * @param mixed $data Data to be hashed.
     * @param string $key Shared secret key.
     * @param boolean $raw Outputs the hash as raw binary data.
     * @return string Return a keyed hash using HMAC.
     */
    public function hmacHash($algorithm, $data, $key, $raw = false) {
        $hmac = hash_hmac($algorithm, $data, $key, $raw);
        return $hmac;
    }

    /**
     * Calculate the Hash of a specific data.
     *
     * @param string $algorithm Hashing algorithm.
     * @param mixed $data Data to be hashed.
     * @param boolean $raw Outputs the hash as raw binary data.
     * @return string Return the Hash of a specific data.
     */
    public function hash($algorithm, $data, $raw = false) {
        $hash = hash($algorithm, $data, $raw);
        return $hash;
    }
}
?>