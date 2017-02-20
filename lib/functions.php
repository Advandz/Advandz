<?php
/**
 * Set all the global functions of the core used throughout the app.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

/**
 * Redirects to an URL.
 *
 * @param  string $url The URL to redirect
 * @return bool   False if the redirects fail
 */
function redirect($uri = WEBDIR)
{
    $parts    = parse_url($uri);
    $relative = true;
    if (substr($uri, 0, 1) == '/') {
        $relative = false;
    }

    // If not scheme is specified, assume http(s)
    if (!isset($parts['scheme'])) {
        $uri = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '').'://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']).($relative ? WEBDIR : '').$uri;
    }

    // Try to redirect
    try {
        header('Location: '.$uri);

        return true;
    } catch (Exception $e) {
        return false;
    }
}
