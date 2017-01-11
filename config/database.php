<?php
/**
 * Initializes all database profiles, and sets the desired database profile
 * to be the active profile.
 *
 * @package Advandz
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

//###############################################################################
// Database lazy connection
//###############################################################################
// Lazy connecting will only establish a connection to the database if one is
// needed. If disabled, a connection will be attempted as soon as a Model is
// requested and a Database profile exists. Some models may not require a DB
// connection so it is recommended to leave this enabled for a best performance.
Configure::set('Database.lazy_connecting', true);
Configure::set('Database.fetch_mode', PDO::FETCH_OBJ);
Configure::set('Database.reuse_connection', true);

//###############################################################################
// Default database profile
//###############################################################################
$default = [
    'driver'        => 'mysql',
    'host'          => 'localhost',
    'port'          => '3306',
    'database'      => 'advandz',
    'user'          => 'root',
    'pass'          => 'root',
    'persistent'    => false,
    'charset_query' => "SET NAMES 'utf8'",
    'options'       => [], // an array of PDO specific options for this connection
];

//###############################################################################
// Database profiles
//###############################################################################
//
// TODO: define more database profiles here
//

//###############################################################################
// Database profile selection
//###############################################################################
// Assign the desired profile based on the current server name. This gives the
// option of having the same codebase run in separate environments (e.g. dev and
// live servers) without making any changes.
$server = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
switch ($server) {
    default:
        Configure::set('Database.profile', $default);
        break;
}

//###############################################################################
// Garbage collector
//###############################################################################
unset($default);
unset($server);
