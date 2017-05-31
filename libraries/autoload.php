<?php
/**
 * Load the necessary libraries for the framework and the vendor
 * libraries.
 *
 * @package Advandz
 * @subpackage Advandz.libraries
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/*
 * Load the Composer autoloader.
 */
@include_once VENDORDIR . 'composer' . DS . 'autoload.php';

/*
 * Autoload the framework libraries
 */
spl_autoload_register(['Advandz\Library\Loader', 'autoload'], true, true);
