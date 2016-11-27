<?php
require __DIR__ . '/src/tracy.php';
use Tracy\Debugger;
Debugger::enable(Debugger::DEVELOPMENT, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'log');
Debugger::$strictMode = false;
?>