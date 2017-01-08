<?php
/**
 * Load the necessary libraries for the framework and the vendor
 * libraries.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/**
 * Load the vendor classes managed by Composer
 */
include_once VENDORDIR . "autoload.php";
spl_autoload_register(['Loader', 'autoload'], true, true);

/**
 * Load the helper classes
 */
$helper_classes = array_diff(scandir(HELPERDIR), ['.', '..']);
foreach ($helper_classes as $class) {
    include_once HELPERDIR . $class . DIRECTORY_SEPARATOR . $class . '.php';
}

/**
 * Load the components classes
 */
$components_classes = array_diff(scandir(COMPONENTDIR), ['.', '..']);
foreach ($components_classes as $class) {
    include_once COMPONENTDIR . $class . DIRECTORY_SEPARATOR . $class . '.php';
}