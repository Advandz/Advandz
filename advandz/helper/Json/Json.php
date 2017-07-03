<?php
/**
 * Provides helper methods for creating JSON strings from arrays and objects.
 *
 * @package Advandz
 * @subpackage Advandz.helpers.json
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Helper;

class Json
{
    /**
     * @var string The JSON string
     */
    private $json = '';

    /**
     * Encodes any Object or Array in to a valid JSON string.
     *
     * @param  mixed  $object The array/object to be encoded
     * @return string The encoded JSON string
     */
    public function jsonEncode($object)
    {
        $json = json_encode($object);

        if ($this->jsonError()) {
            $this->json = $json;

            return $this->json;
        }
    }

    /**
     * Decodes a valid JSON string.
     *
     * @param  string $json The JSON string to be decoded
     * @return mixed  The resultant array/object
     */
    public function jsonDecode($json)
    {
        $json = json_decode($json);

        if ($this->jsonError()) {
            return $json;
        }
    }

    /**
     * Prints the latest succesfully encoded JSON string.
     *
     * @return string The JSON string
     */
    public function printJson()
    {
        echo $this->json;

        return $this->json;
    }

    /**
     * Check the JSON latest error.
     *
     * @throws Exception When the JSON parser returns the last error occurred
     * @return bool      True if is a valid JSON
     */
    private function jsonError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new Exception('The maximum stack depth has been exceeded');
            case JSON_ERROR_STATE_MISMATCH:
                throw new Exception('Invalid JSON string');
            case JSON_ERROR_CTRL_CHAR:
                throw new Exception('Control character error, possibly incorrectly encoded');
            case JSON_ERROR_SYNTAX:
                throw new Exception('Syntax error or invalid JSON string');
            case JSON_ERROR_UTF8:
                throw new Exception('Malformed UTF-8 characters, possibly incorrectly encoded');
            case JSON_ERROR_RECURSION:
                throw new Exception('One or more recursive references in the value to be encoded');
            case JSON_ERROR_UNSUPPORTED_TYPE:
                throw new Exception('A value of a type that cannot be encoded was given');
        }

        return json_last_error() === JSON_ERROR_NONE;
    }
}
