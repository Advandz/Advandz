<?php
/**
 * Spaceman is the command-line interface included with Advandz. It provides a number of helpful
 * commands that can assist you while you build your application.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
use Advandz\Component\Encryption;

class Spaceman
{
    final public static function initialize(array $args)
    {
        // Remove the first argument, useless
        unset($args[0]);

        // Welcome message
        self::printText('Advandz Spaceman 1.0 Development Tool', 'blue');

        // Check given parameters
        if (!empty($args)) {
            
            @$function = $args[1];

            if (!empty($function) && is_callable('Spaceman::'.$function)) {
                // Re-build the array with the parameters
                unset($args[1]);
                $parameters = array_values($args);

                // Call the function
                @call_user_func_array(['Spaceman', $function], $parameters);
            } else {
                self::printText('Invalid or non-existent function called'. (empty($function) ? '.' : ': '.$function), 'red');
                self::help();
            }
        } else {
            self::help();
        }
    }

    final public static function server($port = 8000)
    {
        if (is_numeric($port)) {
            self::printText('Starting Web Server...');
            self::printText('Running PHP '.phpversion().' listening on http://localhost:'.$port.'/');
            self::printText('Press Ctrl-C to shutdown the server.'."\n");

            $command = "php -S localhost:".self::safeArgument($port)." -t ".self::safeArgument(ROOTWEBDIR);
            $output = [];

            $pid = exec($command, $output);

            foreach ($output as $value) {
                self::printText($value);
                flush();
            }
        } else {
            self::printText("\n".'Usage:', 'brown');
            self::printText('server [port]');
        }
    }

    final public static function inspire()
    {
        $quotes = [
            'If you can dream it you can do it. - Walt Disney',
            'The people who are crazy enough to think they can change the world are the ones who do. - Steve Jobs',
            'The best time to plant a tree was 20 years ago. The second best time is now. - Anonymous',
            'The secret to successful hiring is this: look for the people who want to change the world. - Marc Benioff',
            'Always deliver more than expected. - Larry Page',
            'Risk more than others think is safe. Dream more than others think is practical. - Howard Schultz',
            'Do not be embarrassed by your failures, learn from them and start again. - Richard Branson',
            'Fail often so you can succeed sooner. - Tom Kelley',
            'Very little is needed to make a happy life. - Marcus Antoninus',
            'Genius is one percent inspiration and ninety-nine percent perspiration. - Thomas Edison',
            'Coming together is a beginning; keeping together is progress; working together is success. - Henry Ford'
        ];

        self::printText($quotes[rand(0, 10)]);
    }

    final public static function cache($action)
    {
        if ($action == 'flush') {
            self::printText('Flushing cache...');

            // Delete all the cached files
            $files = glob(CACHEDIR.'*');
            foreach($files as $file){
                if(is_file($file)) {
                    unlink($file);
                    self::printText("\n". $file. ' deleted.', 'brown');
                }
            }

            self::printText("\n".'Cache flushed successfully.', 'green');
        } elseif ($action == 'rebuild') {
            self::cache('flush');

            self::printText("\n".'Rebuilding cache...');

            // Call the dispatcher, to generate the cache files
            ob_start();
            Dispatcher::dispatch('/');
            $output = ob_get_contents();
            ob_end_clean();

            if (!empty($output)) {
                self::printText("\n".'Cache rebuilded successfully.', 'green');
            } else {
                self::printText("\n".'Cache rebuilds failed.', 'red');
            }
        } else {
            self::printText("\n".'Usage:', 'brown');
            self::printText('cache ["flush"|"rebuild"]');
        }
    }

    final public static function create($type, $name)
    {
        if ($type == 'plugin' && !empty($name)) {
            // Create directories
            $plugin_file = Loader::fromCamelCase($name);
            $plugin_class = Loader::toCamelCase($name);

            if (mkdir(PLUGINDIR.$plugin_file) && mkdir(PLUGINDIR.$plugin_file.DS.'controllers')
                && mkdir(PLUGINDIR.$plugin_file.DS.'models')
                && mkdir(PLUGINDIR.$plugin_file.DS.'views')
                && mkdir(PLUGINDIR.$plugin_file.DS.'views'.DS.'default')) {
                // Plugin files code
                $main_controller = '<?php

namespace Advandz\App\Controller;

class '.$plugin_class.'Controller extends AppController
{
    public function preAction()
    {
        $this->structure->setDefaultView(APPDIR);
        parent::preAction();
    }

    //
    // TODO: Define any methods, load any models or components or anything else
    // here that you would like to be available to all controllers that extend
    // this special AppController.  This is great for loading certain language
    // files that are used throughout the application.
    // (e.g. $this->loadLang("langfile", "en_us"))
    //
}
';
                $main_model = '<?php

namespace Advandz\App\Model;

class '.$plugin_class.'Model extends AppModel
{
    //
    // TODO: Define any methods that you would like to use in any of your other
    // models that extend this class.
    //
}
';
                $main_plugin_controller = '<?php

namespace Advandz\App\Controller;

class '.$plugin_class.' extends '.$plugin_class.'Controller
{
    public function index()
    {
        //
        // TODO: Define any methods, load any models or components or anything else
        // in the preAction method, that you would like to be available to all methods 
        // of this controller.
        //
    }
}
';
                $structure_knife = '{{!@var content}}';
                $main_knife = '<div class="container padding-top-30 padding-bottom-20">
    <h1>Hello World!</h1>
    <p class="margin-0">This is the "'.$plugin_class.'" plugin.</p>
</div>';
                
                // Create plugin files
                file_put_contents(PLUGINDIR.$plugin_file.DS.$plugin_file.'_controller.php', $main_controller);
                file_put_contents(PLUGINDIR.$plugin_file.DS.$plugin_file.'_model.php', $main_model);

                file_put_contents(PLUGINDIR.$plugin_file.DS.'controllers'.DS.$plugin_file.'.php', $main_plugin_controller);

                file_put_contents(PLUGINDIR.$plugin_file.DS.'views'.DS.'default'.DS.'structure.knife', $structure_knife);
                file_put_contents(PLUGINDIR.$plugin_file.DS.'views'.DS.'default'.DS.$plugin_file.'.knife', $main_knife);
                self::printText("\n".'Plugin created successfully at '.PLUGINDIR, 'green');
            } else {
                self::printText("\n".'The plugin "'.$plugin_class.'" already exists.', 'red');
            }
        } elseif ($type == 'middleware' && !empty($name)) {
            // Create middleware
            $middleware_file = Loader::fromCamelCase($name);
            $middleware_class = Loader::toCamelCase($name);
            $middleware = '<?php

namespace Advandz\App\Middleware;

class '.$middleware_class.'
{
    public function handle($request)
    {
        //
        // TODO: Manage the HTTP request before dispatch it.
        // 
    }

    //
    // TODO: Define any methods, load any models or components or anything else
    // here that you would use for the request management.
    //
}
';
            if (!file_exists(MIDDLEWAREDIR.$middleware_file.'.php')) {
                file_put_contents(MIDDLEWAREDIR.$middleware_file.'.php', $middleware);
                self::printText("\n".'Middleware created successfully at '.MIDDLEWAREDIR, 'green');
            } else {
                self::printText("\n".MIDDLEWAREDIR.$middleware_file.'.php file exists.', 'red');
            }
        } elseif ($type == 'model' && !empty($name)) {
            // Create model
            $model_file = Loader::fromCamelCase($name);
            $model_class = Loader::toCamelCase($name);
            $model = '<?php

namespace Advandz\App\Model;

class '.$model_file.' extends AppModel
{
    //
    // TODO: Define any methods that you would use to process information
    // in your application, load any components or helpers or anything else
    // here that you would be use in your functions.
    // All the public functions will be available to all controllers that imports
    // this model.  
    //
}
';
            if (!file_exists(MODELDIR.$model_file.'.php')) {
                file_put_contents(MODELDIR.$model_file.'.php', $model);
                self::printText("\n".'Model created successfully at '.MODELDIR, 'green');
            } else {
                self::printText("\n".MODELDIR.$model_file.'.php file exists.', 'red');
            }
        } elseif ($type == 'controller' && !empty($name)) {
            // Create controller
            $controller_file = Loader::fromCamelCase($name);
            $controller_class = Loader::toCamelCase($name);
            $controller = '<?php

namespace Advandz\App\Controller;

class '.$controller_class.' extends AppController
{
    //
    // TODO: Define any methods, load any models or components or anything else
    // in the preAction method, that you would like to be available to all methods 
    // of this controller.
    //
}
';
            if (!file_exists(CONTROLLERDIR.$controller_file.'.php')) {
                file_put_contents(CONTROLLERDIR.$controller_file.'.php', $controller);
                self::printText("\n".'Controller created successfully at '.CONTROLLERDIR, 'green');
            } else {
                self::printText("\n".CONTROLLERDIR.$controller_file.'.php file exists.', 'red');
            }
        } elseif ($type == 'facade' && !empty($name)) {
            // Create facade
            $facade_file = Loader::fromCamelCase($name);
            $facade_class = Loader::toCamelCase($name);
            $facade = '<?php

namespace Advandz\App\Facade;

final class '.$facade_class.'
{
    /**
     * Protected constructor to prevent instance creation.
     */
    protected function __construct()
    {
        // Nothing to do
    }
}
';
            if (!file_exists(FACADEDIR.$facade_file.'.php')) {
                file_put_contents(FACADEDIR.$facade_file.'.php', $facade);
                self::printText("\n".'Facade created successfully at '.FACADEDIR, 'green');
            } else {
                self::printText("\n".FACADEDIR.$facade_file.'.php file exists.', 'red');
            }
        } else {
            self::printText("\n".'Usage:', 'brown');
            self::printText('create ["plugin"|"middleware"|"model"|"controller"|"facade"] [name]');
        }
    }

    final public static function key($action) 
    {
        if ($action == 'generate') {
            $encryption = new Encryption();

            self::printText('Key: '.$encryption->generateKey(), 'purple');
        } else {
            self::printText("\n".'Usage:', 'brown');
            self::printText('key ["generate"] [key]');
        }
    }

    final public static function help()
    {
        self::printText("\n".'Usage:', 'brown');
        self::printText('command :[action] [parameters]');

        self::printText("\n".'Available commands:', 'brown');
        self::printText("\033[32m".'server'."\033[0m".'            - Starts a development web server.');
        self::printText("\033[33m".'   :[port]'."\033[0m".'        - The port number to start the server.');

        self::printText("\033[32m".'inspire'."\033[0m".'           - Show an inspiring quote.');

        self::printText("\033[32m".'cache'."\033[0m".'             - Rebuild or flush the cache.');
        self::printText("\033[33m".'   :flush'."\033[0m".'         - Flush the saved cache.');
        self::printText("\033[33m".'   :rebuild'."\033[0m".'       - Rebuilds the cache.');

        self::printText("\033[32m".'create'."\033[0m".'            - Create a new class.');
        self::printText("\033[33m".'   :plugin'."\033[0m".'        - Create a new plugin.');
        self::printText("\033[33m".'   :middleware'."\033[0m".'    - Create a new middleware.');
        self::printText("\033[33m".'   :model'."\033[0m".'         - Create a new model.');
        self::printText("\033[33m".'   :controller'."\033[0m".'    - Create a new controller.');
        self::printText("\033[33m".'   :facade'."\033[0m".'        - Create a new facade.');

        self::printText("\033[32m".'key'."\033[0m".'               - Create a new encryption key.');
        self::printText("\033[33m".'   :generate'."\033[0m".'      - Generates a key.');

        self::printText("\033[32m".'help'."\033[0m".'              - Shows this message.');
    }

    final private static function safeArgument($argument)
    {
        return escapeshellarg($argument);
    }

    final private static function printText($text, $color = 'default')
    {
        $colors = [
            'black' => 30,
            'blue' => 34,
            'green' => 32,
            'cyan' => 36,
            'red' => 31,
            'purple' => 35,
            'brown' => 33,
            'gray' => 37
        ];

        if (array_key_exists($color, $colors)) {
            print "\033[".$colors[$color]."m".$text."\033[0m \n";
        } else {
            print $text."\n";
        }
    }
}
