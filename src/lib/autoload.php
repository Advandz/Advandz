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
@include_once VENDORDIR . "autoload.php";
spl_autoload_register(array('Loader', 'autoload'), true, true);
?>