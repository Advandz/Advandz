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
require __DIR__ . '/src/tracy.php';

use Tracy\Debugger;

Debugger::enable(Debugger::DEVELOPMENT, dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs');
Debugger::$strictMode = false;
?>