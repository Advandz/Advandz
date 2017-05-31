<?php
/**
 * Handles the loading of various files and objects.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2010-2013 Phillips Data, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Cody Phillips <therealclphillips.woop@gmail.com>
 */

namespace Advandz\Library;

use Exception;
use ReflectionClass;

final class Loader
{
    /**
     * Protected constructor to prevent instance creation.
     */
    protected function __construct()
    {
        // Nothing to do
    }

    /**
     * Autoload classes.
     *
     * @param  string $class
     * @return bool   True if loaded, false otherwise
     */
    public static function autoload($class)
    {
        $paths = [
            LIBDIR,
            ROOTWEBDIR . APPDIR,
            CONTROLLERDIR,
            MODELDIR,
            COMPONENTDIR,
            HELPERDIR,
            FACADEDIR
        ];

        $plugin = null;
        if (($c = strpos($class, '.'))) {
            $plugin = self::fromCamelCase(substr($class, 0, $c)) . DIRECTORY_SEPARATOR;
            $class  = substr($class, $c + 1);
        }

        if ($plugin !== null) {
            $paths = [
                PLUGINDIR . $plugin,
                PLUGINDIR . $plugin . 'models' . DIRECTORY_SEPARATOR,
                PLUGINDIR . $plugin . 'controllers' . DIRECTORY_SEPARATOR,
                PLUGINDIR . $plugin . 'components' . DIRECTORY_SEPARATOR,
                PLUGINDIR . $plugin . 'helpers' . DIRECTORY_SEPARATOR,
                PLUGINDIR . $plugin . 'facades' . DIRECTORY_SEPARATOR
            ];
        }

        if (strpos($class, '\\') !== false) { // PSR-4 Autoload
            $class_file = explode('\\', $class);
            $class_file = self::fromCamelCase(end($class_file));
            $file_name  = $class_file . '.php';
        } else { // PSR-0 Autoload
            $class_file = self::fromCamelCase($class);
            $file_name  = $class_file . '.php';
        }

        // Include Files
        foreach ($paths as $path) {
            if (file_exists($path . $file_name)) {
                include $path . $file_name;

                return true;
            } elseif (file_exists($path . $class_file . DIRECTORY_SEPARATOR . $file_name)) {
                include $path . $class_file . DIRECTORY_SEPARATOR . $file_name;

                return true;
            }
        }

        return false;
    }

    /**
     * Loads models, which may or may not exist within a plugin of the same
     * name. First looks in the plugin directory, if no match is found, looks
     * in the models directory.
     *
     * @param  object    $parent The object to which to attach the given models
     * @param  array     $models An array of models to load and initialize
     * @throws Exception
     */
    public static function loadModels(&$parent, $models)
    {
        // Assign all models the controller specified by $parent uses
        if (is_array($models)) {
            foreach ($models as $key => $value) {
                if (is_array($value)) {
                    $model = $key;
                } else {
                    $model = $value;
                    $value = [];
                }

                // Check if the called is Model from a plugin
                $plugin = null;
                if (($c = strpos($model, '.'))) {
                    $plugin = self::fromCamelCase(substr($model, 0, $c)) . DS;
                    $model  = substr($model, $c + 1);
                }

                // Generate namespace and get file name
                if (strpos($model, '\\') !== false) {
                    // A namespace has been provided
                    $model_name      = explode('\\', $model);
                    $model_name      = self::toCamelCase(end($model_name));
                    $model_name_file = self::fromCamelCase($model_name);
                    $namespace       = $model;
                } else {
                    // A class has been provided
                    $model_name      = self::toCamelCase($model);
                    $model_name_file = self::fromCamelCase($model);
                    $namespace       = 'Advandz\\App\\Model\\' . $model_name;
                }

                // Load model file
                if ($plugin) {
                    // Ensure the model exists
                    if (!file_exists(PLUGINDIR . $plugin . 'models' . DS . $model_name_file . '.php')) {
                        throw new Exception('<strong>' . $model_name . '</strong> model not found');
                    }

                    // Include the parent Plugin Model, if it exists
                    self::load(PLUGINDIR . $plugin . substr($plugin, 0, -1) . '_model.php');

                    require_once PLUGINDIR . $plugin . 'models' . DS . $model_name_file . '.php';
                } else {
                    // Ensure the model exists
                    if (!file_exists(MODELDIR . $model_name_file . '.php')) {
                        throw new Exception('<strong>' . $model_name . '</strong> model not found');
                    }

                    require_once MODELDIR . $model_name_file . '.php';
                }

                // Instantiate the model
                $parent->$model_name = call_user_func_array([new ReflectionClass($namespace), 'newInstance'], $value);
            }
        }
    }

    /**
     * Loads the given components, attaching them to the given parent object.
     *
     * @param object $parent     The parent to which to attach the given components
     * @param array  $components An array of components and [optionally] their parameters
     */
    public static function loadComponents(&$parent, $components)
    {
        self::loadAndInitialize($parent, 'component', $components);
    }

    /**
     * Loads the given helpers, attaching them to the given parent object.
     *
     * @param object $parent  The parent to which to attach the given helpers
     * @param array  $helpers An array of helpers and [optionally] their parameters
     */
    public static function loadHelpers(&$parent, $helpers)
    {
        self::loadAndInitialize($parent, 'helper', $helpers);
    }

    /**
     * Convert a string to "CamelCase" from "snake_case".
     *
     * @param  string $str the string to convert
     * @return string the converted string
     */
    public static function toCamelCase($str)
    {
        static $cb_func = null;

        if ($cb_func == null) {
            $cb_func = create_function('$c', 'return strtoupper($c[1]);');
        }

        if (isset($str[0])) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/_([a-z])/', $cb_func, $str);
    }

    /**
     * Convert a string to "snake_case" from "CamelCase".
     *
     * @param  string $str the string to convert
     * @return string the converted string
     */
    public static function fromCamelCase($str)
    {
        static $cb_func = null;

        if ($cb_func == null) {
            $cb_func = create_function('$c', 'return "_" . strtolower($c[1]);');
        }

        if (isset($str[0])) {
            $str[0] = strtolower($str[0]);
        }

        return preg_replace_callback('/([A-Z])/', $cb_func, $str);
    }

    /**
     * Attempts to include the given file, if it exists.
     *
     * @param  string $file The file to include
     * @return bool   Returns true if the file exists and could be included, false otherwise
     */
    public static function load($file)
    {
        if (file_exists($file)) {
            require_once $file;

            return true;
        }

        return false;
    }

    /**
     * Loads an initializes the named objects of the given type to the given parent object.
     * Recognized types include "component" and "helper".
     *
     * @param  object    $parent  The parent object to attach the named objects
     * @param  string    $type    The collection the named objects belong to
     * @param  array     $objects The named objects to load and initialize
     * @throws Exception Throw when invoked with unrecognized $type
     */
    private static function loadAndInitialize(&$parent, $type, $objects)
    {
        switch ($type) {
            case 'component':
                $path = COMPONENTDIR;
                break;
            case 'helper':
                $path = HELPERDIR;
                break;
            default:
                throw new Exception('Unrecognized load type <strong>' . $type . '</strong> specified');
                break;
        }

        if (is_array($objects)) {
            foreach ($objects as $key => $value) {
                if (is_array($value)) {
                    $object = $key;
                } else {
                    $object = $value;
                    $value  = [];
                }

                // Check if the called is object from a plugin
                $plugin = null;
                if (($c = strpos($object, '.'))) {
                    $plugin = self::fromCamelCase(substr($object, 0, $c)) . DS;
                    $object = substr($object, $c + 1);
                }

                if ($plugin) {
                    $dir = PLUGINDIR . $plugin . DS . $type . 's' . DS;
                } else {
                    $dir = $path;
                }

                // Generate namespace and get file name
                if (strpos($object, '\\') !== false) {
                    // A namespace has been provided
                    $object_name      = explode('\\', $object);
                    $object_name      = self::toCamelCase($object_name);
                    $object_name_file = self::fromCamelCase($object_name);
                    $namespace        = $object;
                } else {
                    // A class has been provided
                    $object           = self::fromCamelCase($object);
                    $object_name      = self::toCamelCase($object);
                    $object_name_file = self::fromCamelCase($object);
                    $namespace        = 'Advandz\\' . self::toCamelCase($type) . '\\' . $object_name;
                }

                // Load the object file
                if (file_exists($dir . $object_name_file . '.php')) {
                    // Search for the object in the root object directory
                    require_once $dir . $object_name_file . '.php';
                } elseif (file_exists($dir . $object_name_file . DS . $object_name_file . '.php')) {
                    // The object may also appear in a subdirectory of the same name
                    require_once $dir . $object_name_file . DS . $object_name_file . '.php';
                } else {
                    // If the object can not be found in either location throw an exception
                    throw new Exception('<strong>' . $namespace . '</strong> ' . $type . ' not found');
                }

                // Initialize the object
                $parent->$object_name = call_user_func_array([new ReflectionClass($namespace), 'newInstance'], $value);

                // If is a helper, Link this object with the view and structure view associated with this controller
                if ($type == 'helper') {
                    if (isset($parent->view) && $parent->view instanceof View) {
                        $parent->view->$object_name =&$parent->$object_name;
                    }
                    if (isset($parent->structure) && $parent->structure instanceof View) {
                        $parent->structure->$object_name =&$parent->$object_name;
                    }
                }
            }
        }
    }
}
