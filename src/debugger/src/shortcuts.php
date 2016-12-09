<?php
/**
 * PHP debugging tool, It is an ultimate tool among the diagnostic ones.
 *
 * @package Advandz
 * @subpackage Advandz.debugger
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
if (!function_exists('dump')) {
	function dump($var) {
		array_map('Tracy\Debugger::dump', func_get_args());

		return $var;
	}
}

if (!function_exists('bdump')) {
	function bdump($var) {
		call_user_func_array('Tracy\Debugger::barDump', func_get_args());

		return $var;
	}
}
?>