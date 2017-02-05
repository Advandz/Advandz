<?php
/**
 * The password component provides secure Bcrypt hashing for storing
 * user passwords.
 *
 * @package Advandz
 * @subpackage Advandz.components.password
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

class Password
{
    /**
     * Generates a secure and strong password.
     *
     * @param int $length Length of the password
     * @param bool $numbers Include numbers in the password
     * @param bool $symbols Include symbols in the password
     * @return string A secure password
     */
    public function generate($length = 12, $numbers = true, $symbols = true)
    {
        // Letters
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Numbers
        if ($numbers) {
            $chars .= '0123456789';
        }

        // Symbols
        if ($symbols) {
            $chars .= '@#&%?!-$_^*()[]';
        }

        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        $result = str_shuffle($result);
        $result = substr($result, 0, $length);

        return $result;
    }

    /**
     * Creates a new password hash using a strong one-way hashing algorithm.
     *
     * @param string $password Password to be hashed
     * @param int $cost Denotes the algorithmic cost that should be used
     * @return string The password hash
     */
    public function hash($password, $cost = 10)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hash === false) {
            throw new Exception('Bcrypt hashing not supported');
        }

        return $hash;
    }

    /**
     * Returns an array of information about that hash.
     *
     * @param string $hash The password hash
     * @return array Returns information about the given hash
     */
    public function getHashInfo($hash)
    {
        return password_get_info($hash);
    }

    /**
     * Determine if the work factor used by the hasher has changed since the password was hashed.
     *
     * @param string $hash The password hash
     * @param int $cost Denotes the algorithmic cost that should be used
     * @return bool True if the hash need be rehashed
     */
    public function needsRehash($hash, $cost = 10)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Verifies that a password matches a hash.
     *
     * @param string $password Password to be hashed
     * @param string $hash The created hash
     * @return bool True if the password is valid
     */
    public function verify($password, $hash)
    {
        if (strlen($hash) === 0) {
            return false;
        }

        return password_verify($password, $hash);
    }
}
