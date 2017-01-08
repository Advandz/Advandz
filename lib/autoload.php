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
// Load Composer Vendors Classes
@include_once VENDORDIR . "autoload.php";
spl_autoload_register(['Loader', 'autoload'], true, true);

// Load Helper Classes
$helper_classes = array_diff(scandir(HELPERDIR), array('.', '..'));
foreach ($helper_classes as $class) {
	include_once HELPERDIR . $class . DIRECTORY_SEPARATOR . $class . '.php';
}
?>