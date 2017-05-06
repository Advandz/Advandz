<?php
/**
 * This file transfers control over to the dispatcher which will invoke the
 * appropriate controller. We also handle any exceptions that were not handled
 * elsewhere in the application, so we can end gracefully.
 *
 * @package Advandz
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/*
 * Start benchmark counter.
 */
$start = microtime(true);

/*
 * Initialize the system
 */
try {
    // Load framework
    include dirname(__FILE__) . '/libraries/init.php';

    // Dispatch the Web request
    if (!empty($_SERVER['REQUEST_URI'])) {
        Dispatcher::dispatch($_SERVER['REQUEST_URI']);
    } else {
        // Dispatch the CLI request
        Dispatcher::dispatchCli($argv);
    }
} catch (Exception $e) {
    try {
        // Attempt to raise any error, gracefully
        Dispatcher::raiseError($e);
    } catch (Exception $e) {
        // Print stack trace if Dispatcher can't raise the error
        if (Configure::get('System.debug')) {
            echo $e->getMessage() . ' on line <strong>' . $e->getLine() . '</strong> in <strong>'
                . $e->getFile() . "</strong>\n" . '<br />Printing Stack Trace:<br />'
                . nl2br($e->getTraceAsString());
        } else {
            echo $e->getMessage();
        }
    }
}

/*
 * Stop benchmark counter.
 */
$end = microtime(true);

/*
 * Display rendering time if benchmarking is enabled
 */
if (Configure::get('System.benchmark')) {
    echo 'Execution time: ' . ($end - $start) . ' seconds.';
}
