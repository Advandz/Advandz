<?php
/**
 * This class is invoked by the driver (index.php) and handles dispatching of requests
 * to the proper controller. It extends Controller only so that it can invoke
 * its protected methods.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2010-2013 Phillips Data, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Cody Phillips <therealclphillips.woop@gmail.com>
 */

namespace Advandz\Core;

use Exception;
use Advandz\Helper\Text;

class Dispatcher extends Controller
{
    /**
     * Dispatch a Command Line Interface request.
     *
     * @param array $args All CLI arguments
     */
    public static function dispatchCli($args)
    {
        $request_uri = '/';

        // Build the request URI based on the command line parameters
        $num_args = count($args);
        for ($i = 1; $i < $num_args; $i++) {
            $request_uri .= $args[$i] . '/';
        }

        self::dispatch($request_uri, true);
    }

    /**
     * Dispatch the request to the proper controller.
     *
     * @param  string    $request_uri The request URI string
     * @param  bool      $is_cli      Whether or not this requests is a command line request
     * @throws Exception Thrown when request can not be dispatched or Dispatcher::raiseError can not handle the error
     */
    public static function dispatch($request_uri, $is_cli = false)
    {
        // Load the necessary helpers
        $text = new Text();

        // Get superglobal parameters
        $_post  = $_POST;
        $_files = $_FILES;

        // Fetch parameters from the router
        list($plugin, $controller, $action, $_get, $uri, $uri_str) = array_values(Router::routesTo($request_uri));

        // If caching is enabled, check if this request exists in the cache
        // If so feed it, otherwise continue as normal. Cached pages can only
        // be fed if no post data has been submitted during the request.
        if (Configure::get('Caching.on') && empty($_post)) {
            if (($output = Cache::fetchCache($uri_str))) {
                echo $output;

                return;
            }
        }

        // Initialize the AppModel and AppController, so they can be
        // automatically extended, and then load the respective
        // default language files.
        include_once ROOTWEBDIR . APPDIR . 'AppModel.php';
        include_once ROOTWEBDIR . APPDIR . 'AppController.php';

        Language::loadLang('AppModel');
        Language::loadLang('AppController');

        // Relative path to the plugin directory if it exists
        $plugin_path = null;

        // Check if the called controller is from the app or a plugin
        if (empty($plugin)) {
            // Generate the class name and the namespace name of the
            // controller
            $class     = (is_numeric(substr($controller, 0, 1)) ? '_' : '') . $controller;
            $namespace = 'Advandz\\App\\Controller\\' . $class;

            // Load the controller
            if (file_exists(CONTROLLERDIR . $class . '.php')) {
                include_once CONTROLLERDIR . $class . '.php';
            } else {
                throw new Exception($class . ' is not a valid controller', 404);
            }
        } else {
            // Generate the class name and the namespace name of the
            // main plugin controller
            $plugin    = (is_numeric(substr($plugin, 0, 1)) ? '_' : '') . $plugin;
            $class     = (is_numeric(substr($controller, 0, 1)) ? '_' : '') . $controller;
            $namespace = 'Advandz\\App\\Controller\\' . $class;

            // If an controller exists with the called action, overwrite the action with the controller
            if (file_exists(PLUGINDIR . $plugin . DS . 'Controller' . DS . $text->studlyCase($action) . '.php')) {
                $class     = (is_numeric(substr($action, 0, 1)) ? '_' : '') . $text->studlyCase($action);
                $namespace = 'Advandz\\App\\Controller\\' . $class;
                $action    = $text->camelCase(isset($_get[0]) ? $_get[0] : null);

                // Remove first get element, because has been used as an action
                @array_shift($_get);
            }

            // Load the plguin and the controller
            if (file_exists(PLUGINDIR . $plugin . DS . 'Controller' . DS . $class . '.php')) {
                $plugin_path = str_replace(ROOTWEBDIR, '', PLUGINDIR) . $plugin . DS;

                // Load parent plugin model
                include_once PLUGINDIR . $plugin . DS . $plugin . 'Model.php';

                // Load parent plugin controller
                include_once PLUGINDIR . $plugin . DS . $plugin . 'Controller.php';

                // Load the plugin
                include_once PLUGINDIR . $plugin . DS . 'Controller' . DS . $class . '.php';
            } else {
                throw new Exception($class . ' is not a valid controller from "' . $plugin . '" plugin', 404);
            }
        }

        // If the first character of the controller is a number we must prepend the controller
        // with an underscore.
        if (class_exists($namespace)) {
            $ctrl = new $namespace();
        }

        // Load the default language file for the controller
        Language::loadLang($class);

        // Make the POST/GET/FILES available to the controller
        $ctrl->uri        = $uri;
        $ctrl->uri_str    = $uri_str;
        $ctrl->get        = $_get;
        $ctrl->post       = $_post;
        $ctrl->files      = $_files;
        $ctrl->plugin     = $plugin;
        $ctrl->controller = $controller;
        $ctrl->action     = $action;
        $ctrl->is_cli     = $is_cli;

        // If a plugin path is set, set the path as default view
        if ($plugin_path) {
            $ctrl->setDefaultViewPath($plugin_path);
        }

        // Handle pre action (overwritten by the controller)
        $ctrl->preAction();

        $action_return = null;

        // Invoke the desired action, if it exists
        if ($action != null) {
            if (method_exists($ctrl, $action)) {
                // This action can only be called if it is public
                if (Router::isCallable($ctrl, $action)) {
                    $action_return = $ctrl->$action();
                } // The method is private and thus is not callable
                else {
                    throw new Exception($action . ' is not a callable method in controller "' . $controller . '"', 404);
                }
            } else {
                throw new Exception($action . ' is not a valid method in controller "' . $controller . '"', 404);
            }
        } else {
            // Due the desired action don't exists, then call the default action
            $action_return = $ctrl->index();
        }

        // Handle post action (overwritten by the controller)
        $ctrl->postAction();

        // Only render if the action returned void or something other than false and this is not a CLI request
        if ($action_return !== false && (!$is_cli || Configure::get('System.cli_render_views'))) {
            $ctrl->render();
        }
    }

    /**
     * Print an exception thrown error page.
     *
     * @param  Exception $e An exception thrown
     * @throws Exception Throw our original error, since the error can not be handled cleanly
     * @return bool      False if the redirects fail
     */
    public static function raiseError($e)
    {
        $error_message = null;

        if ($e instanceof UnknownException) {
            $error_message = htmlentities($e->getMessage(), ENT_QUOTES, 'UTF-8') . ' on line <strong>' . $e->getLine() . '</strong> in <strong>' . $e->getFile() . '</strong>';
        } elseif ($e instanceof Exception) {
            if ($e->getCode() == 404 && Configure::get('System.404_forwarding')) {
                // Forward to 404 - page not found.
                try {
                    header('HTTP/1.0 404 Not Found');
                    header('Location: ' . WEBDIR . '404/');

                    return true;
                } catch (Exception $e) {
                    return false;
                }
            } elseif (Configure::get('System.debug')) {
                $error_message = htmlentities($e->getMessage(), ENT_QUOTES, 'UTF-8') . ' on line <strong>' . $e->getLine() . '</strong> in <strong>' . $e->getFile() . "</strong>\n" . '<br /><br /><strong>Printing Stack Trace:</strong><br /><code>' . nl2br($e->getTraceAsString()) . '</code>';
            } elseif (error_reporting() !== 0) {
                $error_message = htmlentities($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }

        try {
            $ctrl = new Controller();
            $ctrl->set('error', $error_message);
            $ctrl->render('error', Configure::get('System.error_view'));
        } catch (Exception $err) {
            // Throw our original error, since the error can not be handled cleanly
            throw $e;
        }
    }
}
