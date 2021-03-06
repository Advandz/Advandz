<?php
/**
 * This class is extended by the various controllers, and makes available
 * methods that allow controllers to interact with views, models, components,
 * helpers, and plugins.
 *
 * @package Advandz
 * @subpackage Advandz.core
 * @copyright Copyright (c) 2010-2013 Phillips Data, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Cody Phillips <therealclphillips.woop@gmail.com>
 */

namespace Advandz\Core;

use Advandz\Helper\Text;

class Controller
{
    /**
     * @var object The structure View for this instance
     */
    public $structure;

    /**
     * @var string Name of the structure view file (overwritable by the controller)
     */
    public $structure_view;

    /**
     * @var object The main View for this instance
     */
    public $view;

    /**
     * @var array All parts of the Routed URI
     */
    public $uri;

    /**
     * @var string Requested URI after being Routed
     */
    public $uri_str;

    /**
     * @var array All GET parameters
     */
    public $get;

    /**
     * @var array All POST data
     */
    public $post;

    /**
     * @var array All FILE data
     */
    public $files;

    /**
     * @var string Name of the plugin invoked by this request (if any)
     */
    public $plugin;

    /**
     * @var string Name of the controller invoked by this request
     */
    public $controller;

    /**
     * @var string Action invoked by this request
     */
    public $action;

    /**
     * @var bool Flag whether this is a CLI request
     */
    public $is_cli;

    /**
     * @var bool Flag used to determine if the view has been rendered. Controller::render() may only be called once
     */
    private $rendered = false;

    /**
     * @var mixed Amount of time in seconds to cache the current request, null otherwise
     */
    private $cache_for = null;

    /**
     * Constructs a new Controller object.
     */
    public function __construct()
    {
        $this->structure_view = Configure::get('System.default_structure');

        // Initialize the structure view
        $this->structure = new View();

        // Initialize the main view
        $this->view = new View();
    }

    /**
     * The default action method, overwritable.
     */
    public function index()
    {
    }

    /**
     * Overwritable method called before the index method, or controller specified action.
     * This method is public to make compatible with PHP 5.1 (due to a bug not fixed until 5.2).
     * It is, however, not a callable action.
     */
    public function preAction()
    {
    }

    /**
     * Overwritable method called after the index method, or controller specified action
     * This method is public to make compatible with PHP 5.1 (due to a bug not fixed until 5.2).
     * It is, however, not a callable action.
     */
    public function postAction()
    {
    }

    /**
     * Invokes View::set() on $this->view.
     *
     * @param mixed $name  The name of the variable to set in this view
     * @param mixed $value The value to assign to the variable set in this view
     * @see View::set()
     */
    final protected function set($name, $value = null)
    {
        $this->view->set($name, $value);
    }

    /**
     * Prints the given template file from the given view.
     * This method is only useful for including a static view in another view.
     * For setting variables in views, or for setting multiple views in a single
     * Page (e.g. partials) see Controller::partial().
     *
     * @param string $file The template file to print
     * @param string $view The view directory to use (null is default)
     * @see Controller::partial()
     */
    final protected function draw($file = null, $view = null)
    {
        $view = new View($file, $view);
        echo $view->fetch();
    }

    /**
     * Returns the given template file using the supplied params from the given view.
     *
     * @param  string $file   The template file to render
     * @param  array  $params An array of parameters to set in the template
     * @param  string $view   The view to find the given template file in
     * @return string The rendered template
     */
    final protected function partial($file, $params = null, $view = null)
    {
        $partial = clone $this->view;

        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $partial->set($key, $value);
            }
        }

        return $partial->fetch($file, $view);
    }

    /**
     * Starts caching for the current request.
     *
     * @param  mixed $time The amount of time to cache for, either an integer (seconds) or a proper strtotime string
     *                     (e.g. "1 hour").
     * @return bool  True if caching is enabled, false otherwise.
     */
    final protected function startCaching($time)
    {
        if (!Configure::get('Caching.on')) {
            return false;
        }

        if (!is_numeric($time)) {
            $time = strtotime($time) - time();
        }
        $this->cache_for = $time;

        return true;
    }

    /**
     * Stops caching for the current request. If invoked, caching will not be performed for this request.
     */
    final protected function stopCaching()
    {
        $this->cache_for = null;
    }

    /**
     * Clears the cache file for the given URI, or for the curren request if no URI is given.
     *
     * @param mixed $uri The request to clear, if not given or false the current request is cleared
     */
    final protected function clearCache($uri = false)
    {
        Cache::clearCache(strtolower($uri ? $uri : $this->uri_str));
    }

    /**
     * Empties the entire cache of all files (directories excluded).
     */
    final protected function emptyCache()
    {
        Cache::emptyCache();
    }

    /**
     * Renders the view with its structure (if set).  The view is set into the structure as $content.
     * This method can only be called once, since it includes the structure when outputting.
     * To render a partial view use Controller::partial().
     *
     * @see Controller::partial()
     * @param string $file The template file to render
     * @param string $view The view directory to look in for the template file.
     */
    final protected function render($file = null, $view = null)
    {
        // Load the necessary helpers
        $text = new Text();

        // If the view is already rendered, stop the rendering
        if ($this->rendered) {
            return;
        }

        $template = $this->structure_view;

        $this->rendered = true;

        // Prepare the structure
        if (strpos($template, DS) > 0) {
            $temp     = explode(DS, $template);
            $template = $temp[1];
            $view     = $temp[0];
        }

        if ($file == null) {
            // Use the view file set for this view (if set)
            if ($this->view->file !== null) {
                $file = $this->view->file;
            } else {
                // Auto-load the view file. These have the format of:
                // [controller_name]_[method_name] for all non-index methods
                $file = $text->snakeCase($this->controller) . ($this->action != null && $this->action != 'index' ? '_' . strtolower($this->action) : '');
            }
        }

        // Render view
        $output = $this->view->fetch($file, $view);

        // Render view in structure
        if ($template != null) {
            $this->structure->set('content', $output);
            $output = $this->structure->fetch($template, $view);
        }

        // Create the cache file, if set
        if ($this->cache_for != null) {
            Cache::writeCache($this->uri_str, $output, $this->cache_for);
        }

        // Output the structure containing the view to standard out
        echo $output;
    }

    /**
     * Initiates a header redirect to the given URI/URL. Automatically prepends
     * WEBDIR to $uri if $uri is relative (e.g. does not start with a '/' and is
     * not a url).
     *
     * @param  string $uri The URI or URL to redirect to. Default is WEBDIR
     * @return bool   False if the redirects fail
     */
    final protected static function redirect($uri = WEBDIR)
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
     * Sets the default view path for this view and its structure view.
     *
     * @param string $path The view path to replace the current view path
     */
    final protected function setDefaultViewPath($path)
    {
        $this->view->setDefaultView($path);
        $this->structure->setDefaultView($path);
    }
}
