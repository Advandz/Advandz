<?php
/**
 * Performs all of the bootstrap operations necessary to begin execution.
 * Includes all of the core library files as well as sets global constants used
 * throughout the app.
 *
 * @package Advandz
 * @subpackage Advandz.libraries
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/*
 * Sets the default error reporting level (everything).  This value should
 * remain as-is.  If the error level needs to be changed it should be done so
 * using Configure::errorReporting(), but only after Configure has been
 * initialized. Simply uncomment the line in /config/core.php.
 */
error_reporting(-1);

/*
 * Sets the version of Advandz in use. [Major].[Minor].[Revision]
 */
define('ADVANDZ_VERSION', '1.0.0');

/*
 * Sets the directory separator used throughout the application. DO NOT use this
 * constant when setting URI paths. THE ONLY VALID directory separator in URIs
 * is / (forward-slash).
 */
define('DS', DIRECTORY_SEPARATOR);

/*
 * Sets the root web directory, which is the absolute path to your web directory.
 */
define('ROOTWEBDIR', dirname(__FILE__) . DS);

/*
 * If you have htaccess running that redirects requests to index.php this must
 * be set to true.  If set to false and no htaccess is present, URIs have the
 * form of /index.php/controller/action/param1/.../paramN
 */
define('HTACCESS', file_exists(ROOTWEBDIR . '.htaccess'));

/*
 * Sets the web directory.  This is the relative path to your web directory, and
 * may include index.php if HTACCESS is set to false.
 */
$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null);
define('WEBDIR', ($webdir = (!HTACCESS ? $script : (($path = dirname($script)) == '/' || $path == DS ? '' : $path)) . '/') == ROOTWEBDIR ? '/' : $webdir);
unset($script, $webdir, $path);

/*
 * The name of the application directory where all models, views, and
 * controllers are placed. Must end with a trailing directory separator.
 */
define('APPDIR', 'app' . DS);

/*
 * Absolute path to the models directory, where all models are stored.
 */
define('MODELDIR', ROOTWEBDIR . APPDIR . 'Model' . DS);

/*
 * Absolute path to the views directory, where all views are stored.
 */
define('VIEWDIR', ROOTWEBDIR . APPDIR . 'Views' . DS);

/*
 * Absolute path to the controllers directory, where all controllers are stored.
 */
define('CONTROLLERDIR', ROOTWEBDIR . APPDIR . 'Controller' . DS);

/*
 * Absolute path to the facades directory, where all facades are stored.
 */
define('FACADEDIR', ROOTWEBDIR . APPDIR . 'Facade' . DS);

/*
 * Absolute path to the middleware directory, where all facades are stored.
 */
define('MIDDLEWAREDIR', ROOTWEBDIR . APPDIR . 'Middleware' . DS);

/*
 * Absolute path to the plugins directory, where plugins are stored.
 */
define('PLUGINDIR', ROOTWEBDIR . APPDIR . 'Plugin' . DS);

/*
 * Absolute path to the language directory, where all language files are stored.
 */
define('LANGDIR', ROOTWEBDIR . 'language' . DS);

/*
 * Absolute path to the core directory.
 */
define('COREDIR', ROOTWEBDIR . 'core' . DS);

/*
 * Absolute path to the config directory, where config files are stored.
 */
define('CONFIGDIR', ROOTWEBDIR . 'config' . DS);

/*
 * Absolute path to the components directory, where all components are stored.
 */
define('COMPONENTDIR', ROOTWEBDIR . 'component' . DS);

/*
 * Absolute path to the helpers directory, where helper files are stored.
 */
define('HELPERDIR', ROOTWEBDIR . 'helper' . DS);

/*
 * Sets the absolute path to the cache directory. Must be writable by the web
 * server to use caching.
 */
define('CACHEDIR', ROOTWEBDIR . 'cache' . DS);

/*
 * Absolute path to the vendors directory, where vendor libraries are stored.
 */
define('VENDORDIR', ROOTWEBDIR . 'vendor' . DS);

/*
 * Include core libraries
 */
require VENDORDIR . 'autoload.php';

/*
 * Load standard library
 */
require ROOTWEBDIR . 'stdlib.php';

/*
 * Load core configuration
 */
require CONFIGDIR . 'core.php';
