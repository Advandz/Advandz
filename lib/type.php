<?php
/**
 * This class helps to define a pseudo-explicit type in variable declaration.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
class Type
{
    /**
     * Set the data type of a variable as String.
     *
     * @param string $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of string
     */
    public static function _string($value = '')
    {
        if (! is_string($value)) {
            throw new Exception('Argument passed must be an instance of string');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Integer.
     *
     * @param int $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of integer
     */
    public static function _int($value = 0)
    {
        if (! is_int($value)) {
            throw new Exception('Argument passed must be an instance of integer');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Float.
     *
     * @param float $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of float
     */
    public static function _float($value = 0.0)
    {
        if (! is_float($value)) {
            throw new Exception('Argument passed must be an instance of float');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Double.
     *
     * @param float $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of double
     */
    public static function _double($value = 0.0)
    {
        if (! is_float($value)) {
            throw new Exception('Argument passed must be an instance of double');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Boolean.
     *
     * @param bool $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of boolean
     */
    public static function _bool($value = false)
    {
        if (! is_bool($value)) {
            throw new Exception('Argument passed must be an instance of boolean');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Array.
     *
     * @param array $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an instance of array
     */
    public static function _array($value = [])
    {
        if (! is_array($value)) {
            throw new Exception('Argument passed must be an instance of array');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Object.
     *
     * @param string $value The defined value of the variable
     * @return string The value
     * @throws Exception When the argument passed is not an object
     */
    public static function _object($value = null)
    {
        if (is_null($value)) {
            $value = new stdClass();
        }

        if (! is_object($value)) {
            throw new Exception('Argument passed must be an object');
        }

        return $value;
    }

    /**
     * Set the data type of a variable as Null.
     *
     * @return null
     */
    public static function _null()
    {
        return null;
    }
}
