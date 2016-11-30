<?php
/**
 * Provides a set of static methods to aid in the use of multi-language support.
 * Supports the use of multiple simultaneous languages, including a default
 * (fallback) language. When the definition can not be found in the set of
 * primary keys, the default is used instead.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
use Minphp\Language\Language as FrameworkLanguage;

class Language extends FrameworkLanguage {
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
    public static function getText($lang_key, $return = false) {
        $allow_passthrough = self::$allow_passthrough;
        $default_language = self::$default_language;

        self::allowPassthrough(Configure::get('Language.allow_pass_through'));
        self::setDefaultLanguage(Configure::get('Language.default'));

        $args = func_get_args();
        $result = call_user_func_array(array(parent, "getText"), $args);

        self::allowPassthrough($allow_passthrough);
        self::setDefaultLanguage($default_language);
        return $result;
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
    public static function loadLang($lang_file, $language = null, $lang_dir = LANGDIR)
    {
        $default_language = self::$default_language;

        self::setDefaultLanguage(Configure::get('Language.default'));
        parent::loadLang($lang_file, $language, $lang_dir);

        self::setDefaultLanguage($default_language);
    }
}
?>