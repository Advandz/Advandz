<?php
/**
 * This class helps to define a explicit type in variable declaration.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
class Type {
    /**
     * Set the data type of a variable as String
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function String($value = '') {
        if(!is_string($value))
            throw new Exception("Invalid data type, String expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Integer
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Integer($value = 0) {
        if(!is_int($value))
            throw new Exception("Invalid data type, Integer expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Float
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Float($value = 0.0) {
        if(!is_float($value))
            throw new Exception("Invalid data type, Float expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Double
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Double($value = 0.0) {
        if(!is_double($value))
            throw new Exception("Invalid data type, Double expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Boolean
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Boolean($value = false) {
        if(!is_bool($value))
            throw new Exception("Invalid data type, Boolean expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Array
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Array($value = []) {
        if(!is_array($value))
            throw new Exception("Invalid data type, Array expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Object
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Object(stdClass $value = null) {
        if(!is_object($value))
            throw new Exception("Invalid data type, Object expected");

        return $value;
    }

    /**
     * Set the data type of a variable as Null
     *
     * @param string $value The defined value of the variable
     * @return string The value
     */
    static public function Null() {
        return null;
    }
}
?>