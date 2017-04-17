<?php
/**
 * This class helps you quickly write simple yet powerful web services and APIs.
 *
 * @package Advandz
 * @subpackage Advandz.app.facades
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\App\Facade;

final class App
{
    /**
     * Protected constructor to prevent instance creation.
     */
    protected function __construct()
    {
        // Nothing to do
    }

    /**
     * Process the GET requests.
     *
     * @param string   $pattern  The URI pattern to execute the request
     * @param callable $callable The anonymous function to process the request
     */
    final public static function get($pattern, $callable)
    {
        if (self::getMethod() === 'GET' && self::validatePattern($pattern)) {
            if (is_callable($callable)) {
                $parameters = self::parsePattern($pattern);

                call_user_func_array($callable, $parameters);
            }
        }
    }

    /**
     * Process the POST requests.
     *
     * @param string   $pattern  The URI pattern to execute the request
     * @param callable $callable The anonymous function to process the request
     */
    final public static function post($pattern, $callable)
    {
        if (self::getMethod() === 'POST' && self::validatePattern($pattern)) {
            if (is_callable($callable)) {
                $parameters = self::parsePattern($pattern);

                call_user_func_array($callable, $parameters);
            }
        }
    }

    /**
     * Process the PUT requests.
     *
     * @param string   $pattern  The URI pattern to execute the request
     * @param callable $callable The anonymous function to process the request
     */
    final public static function put($pattern, $callable)
    {
        if (self::getMethod() === 'PUT' && self::validatePattern($pattern)) {
            if (is_callable($callable)) {
                $parameters = self::parsePattern($pattern);

                call_user_func_array($callable, $parameters);
            }
        }
    }

    /**
     * Process the PATCH requests.
     *
     * @param string   $pattern  The URI pattern to execute the request
     * @param callable $callable The anonymous function to process the request
     */
    final public static function patch($pattern, $callable)
    {
        if (self::getMethod() === 'PATCH' && self::validatePattern($pattern)) {
            if (is_callable($callable)) {
                $parameters = self::parsePattern($pattern);

                call_user_func_array($callable, $parameters);
            }
        }
    }

    /**
     * Process the DELETE requests.
     *
     * @param string   $pattern  The URI pattern to execute the request
     * @param callable $callable The anonymous function to process the request
     */
    final public static function delete($pattern, $callable)
    {
        if (self::getMethod() === 'DELETE' && self::validatePattern($pattern)) {
            if (is_callable($callable)) {
                $parameters = self::parsePattern($pattern);

                call_user_func_array($callable, $parameters);
            }
        }
    }

    /**
     * Returns the HTTP method used to access this page.
     */
    final public static function getMethod()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    /**
     * Returns the URI which was given in order to access this page.
     */
    final public static function getUri()
    {
        $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
        if (substr($uri, 0, strlen(WEBDIR)) == WEBDIR) {
            return substr($uri, strlen(WEBDIR), strlen($uri));
        } else {
            return $uri;
        }
    }

    /**
     * Redirects to an URL.
     *
     * @param  string $url The URL to redirect
     * @return bool   False if the redirects fail
     */
    final public static function redirect($uri = WEBDIR)
    {
        $parts    = parse_url($uri);
        $relative = true;
        if (substr($uri, 0, 1) == '/') {
            $relative = false;
        }

        // If not scheme is specified, assume http(s)
        if (!isset($parts['scheme'])) {
            $uri = 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '') . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']) . ($relative ? WEBDIR : '') . $uri;
        }

        // Try to redirect
        try {
            header('Location: ' . $uri);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Converts the URI in to an array containing the parameters of the pattern.
     *
     * @param  string $pattern The URI pattern to parse
     * @return array  An array containing the parameters
     */
    final private static function parsePattern($pattern)
    {
        $blocks = explode('/', trim($pattern, '/'));
        $uri    = explode('/', self::getUri());

        $result = [];

        $i = 0;
        foreach ($blocks as $key => $block) {
            preg_replace_callback('/\\{([^{}]+)\\}/', function ($match) use (&$result, &$key, &$uri) {
                $result[$match[1]] = (empty($uri[$key]) ? null : $uri[$key]);
            }, $block);
            $i++;
        }

        return $result;
    }

    /**
     * Validates if a URI Pattern is the same of the request.
     *
     * @param  string $pattern The URI pattern to validate
     * @return bool   True if is valid, false if is not valid for the request
     */
    final private static function validatePattern($pattern)
    {
        $blocks = explode('/', trim($pattern, '/'));
        $uri    = explode('/', self::getUri());

        foreach ($blocks as $key => $value) {
            if ($value !== $uri[$key] && !(bool) preg_match('/\\{([^{}]+)\\}/', $value)) {
                return false;
            }
        }

        return true;
    }
}
