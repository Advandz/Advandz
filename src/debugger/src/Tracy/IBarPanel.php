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
namespace Tracy;

/**
 * Custom output for Debugger.
 */
interface IBarPanel {
	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 */
	function getTab();

	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 */
	function getPanel();
}

?>