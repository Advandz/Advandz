<?php
/**
 * Standard Library, where all misc. and compatibility functions/handlers are
 * placed.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/**
 * Set the default timezone to satisfy PHP strict standards on servers with this
 * capability.
 */
if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set(@date_default_timezone_get());
}

/**
 * Send all errors generated by PHP to UnknownException::setErrorHandler
 *
 * @see UnknownException::setErrorHandler()
 */
set_error_handler(["UnknownException", "setErrorHandler"]);

/**
 * Send all uncaught exceptions to UnknownException::setExceptionHandler
 *
 * @see UnknownException::setExceptionHandler()
 */
set_exception_handler(["UnknownException", "setExceptionHandler"]);

/**
 * Send all capturable Fatal errors to UnknownException::setFatalErrorHandler
 *
 * @see UnknownException::setFatalErrorHandler()
 */
register_shutdown_function(["UnknownException", "setFatalErrorHandler"]);