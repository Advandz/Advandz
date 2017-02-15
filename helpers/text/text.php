<?php
/**
 * Provides utility methods to assist in manipulating strings.
 *
 * @package Advandz
 * @subpackage Advandz.helpers.string
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Helper;

class Text
{
    /**
     * @var array The plural rules
     */
    private $plural = [
        '/(quiz)$/i'                     => "$1zes",
        '/^(ox)$/i'                      => "$1en",
        '/([m|l])ouse$/i'                => "$1ice",
        '/(matr|vert|ind)ix|ex$/i'       => "$1ices",
        '/(matr|vert|ind)iz|ice$/i'      => "$1ices",
        '/(x|ch|ss|sh)$/i'               => "$1es",
        '/([^aeiouy]|qu)y$/i'            => "$1ies",
        '/(hive)$/i'                     => "$1s",
        '/(?:([^f])fe|([lr])f)$/i'       => "$1$2ves",
        '/(shea|lea|loa|thie)f$/i'       => "$1ves",
        '/sis$/i'                        => "ses",
        '/([ti])um$/i'                   => "$1a",
        '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
        '/(bu)s$/i'                      => "$1ses",
        '/(alias)$/i'                    => "$1es",
        '/(octop)us$/i'                  => "$1i",
        '/(ax|test)is$/i'                => "$1es",
        '/(us)$/i'                       => "$1es",
        '/s$/i'                          => "s",
        '/$/'                            => "s"
    ];

    /**
     * @var array The singular rules
     */
    private $singular = [
        '/(quiz)zes$/i'                                                    => "$1",
        '/(matr)ices$/i'                                                   => "$1ix",
        '/(vert|ind)ices$/i'                                               => "$1ex",
        '/^(ox)en$/i'                                                      => "$1",
        '/(alias)es$/i'                                                    => "$1",
        '/(octop|vir)i$/i'                                                 => "$1us",
        '/(cris|ax|test)es$/i'                                             => "$1is",
        '/(shoe)s$/i'                                                      => "$1",
        '/(o)es$/i'                                                        => "$1",
        '/(bus)es$/i'                                                      => "$1",
        '/([m|l])ice$/i'                                                   => "$1ouse",
        '/(x|ch|ss|sh)es$/i'                                               => "$1",
        '/(m)ovies$/i'                                                     => "$1ovie",
        '/(s)eries$/i'                                                     => "$1eries",
        '/([^aeiouy]|qu)ies$/i'                                            => "$1y",
        '/([lr])ves$/i'                                                    => "$1f",
        '/(tive)s$/i'                                                      => "$1",
        '/(hive)s$/i'                                                      => "$1",
        '/(li|wi|kni)ves$/i'                                               => "$1fe",
        '/(shea|loa|lea|thie)ves$/i'                                       => "$1f",
        '/(^analy)ses$/i'                                                  => "$1sis",
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis",
        '/([ti])a$/i'                                                      => "$1um",
        '/(n)ews$/i'                                                       => "$1ews",
        '/(h|bl)ouses$/i'                                                  => "$1ouse",
        '/(corpse)s$/i'                                                    => "$1",
        '/(us)es$/i'                                                       => "$1",
        '/(business)$/i'                                                   => "$1",
        '/s$/i'                                                            => ""
    ];

    /**
     * @var array The irregular phrases
     */
    private $irregular = [
        'move'   => 'moves',
        'foot'   => 'feet',
        'goose'  => 'geese',
        'sex'    => 'sexes',
        'child'  => 'children',
        'man'    => 'men',
        'tooth'  => 'teeth',
        'person' => 'people',
        'valve'  => 'valves'
    ];

    /**
     * @var array The uncountable phrases
     */
    private $uncountable = [
        'sheep',
        'fish',
        'deer',
        'series',
        'species',
        'money',
        'rice',
        'information',
        'equipment',
        'milk',
        'water',
        'advice',
        'chaos',
        'snow',
        'rain',
        'weather',
        'sleep'
    ];

    /**
     * Pluralize all the singular english words.
     *
     * @param string $string The text to be pluralized
     * @return string The resultant text
     */
    public function pluralize($string)
    {
        // Check if the singular and plural are the same word
        if (in_array(strtolower($string), $this->uncountable)) {
            return $string;
        }

        // Check for irregular singular forms
        foreach ($this->irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // Check for matches using regular expressions
        foreach ($this->plural as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Singularize all the plural english words.
     *
     * @param string $string The text to be singularized
     * @return string The resultant text
     */
    public function singularize($string)
    {
        // Check if the singular and plural are the same word
        if (in_array(strtolower($string), $this->uncountable)) {
            return $string;
        }

        // Check for irregular plural forms
        foreach ($this->irregular as $result => $pattern) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // Check for matches using regular expressions
        foreach ($this->singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * Converts a text to camelCase.
     *
     * @param string $text The text to be converted
     * @return string The camelCase text
     */
    public function camelCase($text)
    {
        $result = '';

        $text = $this->snakeCase($text);
        $text = explode('_', $text);

        foreach ($text as $word) {
            $result .= ucfirst($word);
        }

        return lcfirst($result);
    }

    /**
     * Converts a text to StudlyCase.
     *
     * @param string $text The text to be converted
     * @return string The StudlyCase text
     */
    public function studlyCase($text)
    {
        $text = $this->camelCase($text);

        return ucfirst($text);
    }

    /**
     * Converts a text to snake_case.
     *
     * @param string $text The text to be converted
     * @return string The snake_case text
     */
    public function snakeCase($text)
    {
        $text = trim($text);
        $text = str_replace(' ', '_', $text);
        $text = str_replace('-', '_', $text);

        return strtolower($text);
    }

    /**
     * Converts a text to kebab-case.
     *
     * @param string $text The text to be converted
     * @return string The kebab-case text
     */
    public function kebabCase($text)
    {
        $text = $this->snakeCase($text);
        $text = str_replace('_', '-', $text);

        return $text;
    }

    /**
     * Converts a text to a URL friendly string.
     *
     * @param string $text The text to be converted
     * @return string The kebab-case text
     */
    public function uri($text)
    {
        $text = preg_replace("/[^A-Za-z0-9 ]/", '', $text);

        return $this->kebabCase($text);
    }

    /**
     * Converts a text to a title case.
     *
     * @param string $text The text to be converted
     * @return string The title case text
     */
    public function title($text)
    {
        return ucwords($text);
    }

    /**
     * Capitalizes a text.
     *
     * @param string $text The text to be capitalized
     * @return string The capitalized text
     */
    public function capitalize($text)
    {
        return ucfirst($text);
    }

    /**
     * Lowercase a text.
     *
     * @param string $text The text to be lowercased
     * @return string The lowercased text
     */
    public function lowercase($text)
    {
        return strtolower($text);
    }

    /**
     * Uppercase a text.
     *
     * @param string $text The text to be uppercased
     * @return string The uppercased text
     */
    public function uppercase($text)
    {
        return strtoupper($text);
    }

    /**
     * Limits the number of characters in a string.
     *
     * @param string $text The text to be truncated
     * @param int $limit Maximum number of resulting characters
     * @return string The truncated text
     */
    public function truncate($text, $limit)
    {
        return substr($text, 0, $limit) . '...';
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param string $haystack The string to search in
     * @param string $needle The needle to search
     * @return bool True if the needle has been found, false otherwise
     */
    public function contains($haystack, $needle)
    {
        return ($needle != '' && mb_strpos($haystack, $needle) !== false);
    }

    /**
     * Censor words from a text.
     *
     * @param string $text The text string
     * @param array $words The array of censored words
     * @return string The clean text
     */
    public function censorWords($text, array $words, $replacement = '****')
    {
        foreach ($words as $word) {
            $text = str_replace($word, $replacement, $text);
        }

        return $text;
    }
}
