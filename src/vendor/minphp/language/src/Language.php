<?php
namespace Minphp\Language;

/**
 * Provides a set of static methods to aid in the use of multi-language support.
 * Supports the use of multiple simultaneous languages, including a default
 * (fallback) language. When the definition can not be found in the set of
 * primary keys, the default is used instead.
 *
 * This class makes use of the following Configure class options:
 *
 * Language.default - Defines the default (fallback) language (ISO 639-1/2) e.g. "en_us"
 * Language.allow_pass_through - true/false allows keys without a definition to be passed through
 */
class Language
{
    /**
     * @var array An associative array containing name of all of the language files loaded
     * and the language they pertain to
     */
    protected static $lang_files;
    /**
     * @var array The text for the given language
     */
    protected static $lang_text;
    /**
     * @var string The current language (ISO 639-1/2) e.g. "en_us"
     */
    protected static $current_language;
    /**
     * @var string The default language (ISO 639-1/2)
     */
    protected static $default_language = "en_us";
    /**
     * @var boolean True to allow language keys to be displayed if no match found
     */
    protected static $allow_passthrough = false;
    /**
     * @var string The path to the default language directory
     */
    protected static $default_dir = null;

    /**
     * Set default language
     *
     * @param string $default_language
     */
    public static function setDefaultLanguage($default_language)
    {
        self::$default_language = $default_language;
    }

    /**
     * Set the default language directory
     *
     * @param string $dir
     */
    public static function setDefaultDir($dir)
    {
        self::$default_dir = $dir;
    }

    /**
     * Allow language terms to be returned if no definition found
     *
     * @param boolean $allow_passthrough
     */
    public static function allowPassthrough($allow_passthrough)
    {
        self::$allow_passthrough = $allow_passthrough;
    }

    /**
     * Alias of Language::getText()
     * @see Language::getText()
     *
     * @param string $lang_key The language key identifier for this requested text
     * @param boolean $return Whether to return the text or output it
     * @param mixed $... Values to substitute in the language result. Uses
     *  sprintf(). If parameter is an array, only that value is passed to sprintf().
     */
    // @codingStandardsIgnoreStart
    public static function _($lang_key, $return = false)
    {
        // @codingStandardsIgnoreEnd
        $args = func_get_args();
        return call_user_func_array(array("\Minphp\Language\Language", "getText"), $args);
    }

    /**
     * Fetches text from the loaded language file.  Will search the preferred
     * language file first, if not found in there, then will search the default
     * language file for the $lang_key text.
     *
     * @param string $lang_key The language key identifier for this requested text
     * @param boolean $return Whether to return the text or output it
     * @param mixed $... Values to substitute in the language result. Uses
     *  sprintf(). If parameter is an array, only that value is passed to sprintf().
     */
    public static function getText($lang_key, $return = false)
    {
        $language = self::$current_language != null
            ? self::$current_language
            : self::$default_language;

        $output = "";

        // If the text defined exists, use it
        if (isset(self::$lang_text[$language][$lang_key])) {
            $output = self::$lang_text[$language][$lang_key];
        } elseif (isset(self::$lang_text[self::$default_language][$lang_key])) {
            // If the text defined did not exist in the set language, look for it
            // in the default language
            $output = self::$lang_text[self::$default_language][$lang_key];
        } elseif (self::$allow_passthrough) {
            $output = $lang_key;
        }

        $argc = func_num_args();
        if ($argc > 2) {
            $args = array_slice(func_get_args(), 2, $argc-1);
            // If printf args are passed as an array use those instead.
            if (is_array($args[0])) {
                $args = $args[0];
            }
            array_unshift($args, $output);

            $output = call_user_func_array("sprintf", $args);
        }

        if ($return) {
            return $output;
        }
        echo $output;
    }

    /**
     * Loads a language file whose properties may then be invoked.
     *
     * @param mixed $lang_file A string as a single language file or array containing a list of language files to load
     * @param string $language The ISO 639-1/2 language to load the $lang_file
     *  for (e.g. en_us), default is "Language.default" config value
     * @param string $lang_dir The directory from which to load the given
     *  language file(s), defaults to default directory
     */
    public static function loadLang($lang_file, $language = null, $lang_dir = null)
    {
        if ($language === null) {
            $language = self::$current_language;
        }

        if ($lang_dir === null) {
            $lang_dir = self::$default_dir;
        }

        if (is_array($lang_file)) {
            $num_lang_files = count($lang_file);
            for ($i=0; $i<$num_lang_files; $i++) {
                self::loadLang($lang_file[$i], $language, $lang_dir);
            }
            return;
        }

        // Check if the language file in this language has already been loaded
        if (isset(self::$lang_files[$lang_dir . $lang_file])
            && in_array($language, self::$lang_files[$lang_dir . $lang_file])
        ) {
            return;
        }

        $load_success = true;

        // Fetch $lang from the language file, if it exists
        if (file_exists($lang_dir . $language . DIRECTORY_SEPARATOR . $lang_file)) {
            include_once $lang_dir . $language . DIRECTORY_SEPARATOR . $lang_file;
        } elseif (file_exists($lang_dir . $language . DIRECTORY_SEPARATOR . $lang_file . ".php")) {
            include_once $lang_dir . $language . DIRECTORY_SEPARATOR . $lang_file . ".php";
        } else {
            $load_success = false;
        }

        if ($load_success) {
            self::$lang_files[$lang_dir . $lang_file][] = $language;

            if (isset($lang) && is_array($lang)) {
                if (!isset(self::$lang_text[$language])) {
                    self::$lang_text[$language] = array();
                }

                // Set the text for this language
                foreach ($lang as $key => $text) {
                    self::$lang_text[$language][$key] = $text;
                }

                // Load the text for the default language as well so we have that to fall back on
                if ($language != self::$default_language) {
                    self::loadLang($lang_file, self::$default_language, $lang_dir);
                }
            }
            // free up memory occupied by the $lang array, since it has already
            // been loaded into the appropriate class variable
            unset($lang);
        } elseif ($language != self::$default_language) {
            // If the language just attemped did not load and this was not the
            // default language, then attempt to load the default language
            self::loadLang($lang_file, self::$default_language, $lang_dir);
        }
    }

    /**
     * Sets the language to load when not explicitly defined in the requested method
     *
     * @param string $language The ISO 639-1/2 language to use (e.g. en_us) for
     *  all future requests if not explicitly given to the requested method
     * @return string The previously set language, null if not previously defined
     */
    public static function setLang($language)
    {
        $prev_lang = self::$current_language;

        self::$current_language = $language;

        return $prev_lang;
    }
}
