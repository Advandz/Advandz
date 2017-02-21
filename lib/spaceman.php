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
use Advandz\Component\Filesystem;

class Spaceman
{
    /**
     * Initializes the Spaceman script.
     *
     * @param array $args An array containing the CLI arguments
     */
    final public static function initialize(array $args)
    {
        // Initialize only if it is called through the CLI
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            passthru('clear');
            unset($args[0]);

            // Welcome message
            self::printText('Advandz Spaceman 1.0 Development Tool'."\n", 'blue');

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
                    self::printText('Invalid or non-existent function called'.(empty($function) ? '.' : ': '.$function), 'red');
                    self::help();
                }
            } else {
                self::help();
            }
        }
    }

    /**
     * Starts a development server.
     *
     * @param int $port The port to run the web server
     */
    final public static function server($port = 8000)
    {
        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            if (is_numeric($port)) {
                self::printText('Starting Web Server...');
                self::printText('Running PHP '.phpversion().' listening on http://localhost:'.$port.'/');
                self::printText('Press Ctrl-C to shutdown the server.'."\n");

                $command = 'php -S localhost:'.self::safeArgument($port).' -t '.self::safeArgument(ROOTWEBDIR);
                $output  = [];

                $pid = exec($command, $output);

                foreach ($output as $value) {
                    self::printText($value);
                    flush();
                }
            } else {
                self::printText('Usage:', 'brown');
                self::printText('server [port]');
            }
        }
    }

    /**
     * Prints an inspirational message.
     *
     * @return string An inspirational message.
     */
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

        return $quotes[rand(0, 10)];
    }

    /**
     * Rebuilds or flush the stored cache.
     *
     * @param string $action The cache action to execute
     * @return bool True if the action has been executed successfully
     */
    final public static function cache($action)
    {
        if ($action == 'flush') {
            self::printText('Flushing cache...');

            // Delete all the cached files
            $files = glob(CACHEDIR.'*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    self::printText($file.' deleted.', 'brown');
                }
            }

            self::printText('Cache flushed successfully.', 'green');

            return true;
        } elseif ($action == 'rebuild') {
            self::cache('flush');

            self::printText('Rebuilding cache...');

            // Call the dispatcher, to generate the cache files
            ob_start();
            Dispatcher::dispatch('/');
            $output = ob_get_contents();
            ob_end_clean();

            if (!empty($output)) {
                self::printText('Cache rebuilded successfully.', 'green');

                return true;
            } else {
                self::printText('Cache rebuilds failed.', 'red');

                return false;
            }
        } else {
            self::printText('Usage:', 'brown');
            self::printText('cache ["flush"|"rebuild"]');

            return false;
        }
    }

    /**
     * Creates a new class in the app.
     *
     * @param string $type The class type to create
     * @param string $name The new class name
     * @return bool True if the class has been created successfully
     */
    final public static function create($type, $name)
    {
        $filesystem = new Filesystem();

        if ($type == 'plugin' && !empty($name)) {
            // Create directories
            $plugin_file  = Loader::fromCamelCase($name);
            $plugin_class = Loader::toCamelCase($name);

            if (mkdir(PLUGINDIR.$plugin_file) && mkdir(PLUGINDIR.$plugin_file.DS.'controllers') && mkdir(PLUGINDIR.$plugin_file.DS.'models') && mkdir(PLUGINDIR.$plugin_file.DS.'views') && mkdir(PLUGINDIR.$plugin_file.DS.'views'.DS.'default')) {
                // Plugin files code
                $main_controller        = '<?php'."\n\n".'namespace Advandz\App\Controller;'."\n\n".'class '.$plugin_class.'Controller extends AppController'."\n".'{'."\n".'    public function preAction()'."\n".'    {'."\n".'        $this->structure->setDefaultView(APPDIR);'."\n".'        parent::preAction();'."\n".'    }'."\n".''."\n".'    //'."\n".'    // TODO: Define any methods, load any models or components or anything else'."\n".'    // here that you would like to be available to all controllers that extend'."\n".'    // this special AppController.  This is great for loading certain language'."\n".'    // files that are used throughout the application.'."\n".'    // (e.g. $this->loadLang("langfile", "en_us"))'."\n".'    //'."\n".'}';
                $main_model             = '<?php'."\n\n".'namespace Advandz\App\Model;'."\n\n".'class '.$plugin_class.'Model extends AppModel'."\n".'{'."\n".'    //'."\n".'    // TODO: Define any methods that you would like to use in any of your other'."\n".'    // models that extend this class.'."\n".'    //'."\n".'}';
                $main_plugin_controller = '<?php'."\n\n".'namespace Advandz\App\Controller;'."\n\n".'class '.$plugin_class.' extends '.$plugin_class.'Controller'."\n".'{'."\n".'    public function index()'."\n".'    {'."\n".'        //'."\n".'        // TODO: Define any methods, load any models or components or anything else'."\n".'        // in the preAction method, that you would like to be available to all methods '."\n".'        // of this controller.'."\n".'        //'."\n".'    }'."\n".'}';
                $structure_knife        = '{{!@var content}}';
                $main_knife             = '<div class="container padding-top-30 padding-bottom-20">'."\n".'    <h1>Hello World!</h1>'."\n".'    <p class="margin-0">This is the "'.$plugin_class.'" plugin.</p>'."\n".'</div>';

                // Create plugin files
                $filesystem->saveFile(PLUGINDIR.$plugin_file.DS.$plugin_file.'_controller.php', $main_controller);
                $filesystem->saveFile(PLUGINDIR.$plugin_file.DS.$plugin_file.'_model.php', $main_model);

                $filesystem->saveFile(PLUGINDIR.$plugin_file.DS.'controllers'.DS.$plugin_file.'.php', $main_plugin_controller);

                $filesystem->saveFile(PLUGINDIR.$plugin_file.DS.'views'.DS.'default'.DS.'structure.knife', $structure_knife);
                $filesystem->saveFile(PLUGINDIR.$plugin_file.DS.'views'.DS.'default'.DS.$plugin_file.'.knife', $main_knife);
                self::printText('Plugin created successfully at '.PLUGINDIR, 'green');

                return true;
            } else {
                self::printText('The plugin "'.$plugin_class.'" already exists.', 'red');

                return false;
            }
        } elseif ($type == 'middleware' && !empty($name)) {
            // Create middleware
            $middleware_file  = Loader::fromCamelCase($name);
            $middleware_class = Loader::toCamelCase($name);
            $middleware       = '<?php'."\n\n".'namespace Advandz\App\Middleware;'."\n\n".'class '.$middleware_class."\n".'{'."\n".'    public function handle($request)'."\n".'    {'."\n".'        //'."\n".'        // TODO: Manage the HTTP request before dispatch it.'."\n".'        // '."\n".'    }'."\n".''."\n".'    //'."\n".'    // TODO: Define any methods, load any models or components or anything else'."\n".'    // here that you would use for the request management.'."\n".'    //'."\n".'}';

            if (!file_exists(MIDDLEWAREDIR.$middleware_file.'.php')) {
                $filesystem->saveFile(MIDDLEWAREDIR.$middleware_file.'.php', $middleware);
                self::printText('Middleware created successfully at '.MIDDLEWAREDIR, 'green');

                return true;
            } else {
                self::printText(MIDDLEWAREDIR.$middleware_file.'.php file exists.', 'red');

                return false;
            }
        } elseif ($type == 'model' && !empty($name)) {
            // Create model
            $model_file  = Loader::fromCamelCase($name);
            $model_class = Loader::toCamelCase($name);
            $model       = '<?php'."\n\n".'namespace Advandz\App\Model;'."\n\n".'class '.$model_file.' extends AppModel'."\n".'{'."\n".'    //'."\n".'    // TODO: Define any methods that you would use to process information'."\n".'    // in your application, load any components or helpers or anything else'."\n".'    // here that you would be use in your functions.'."\n".'    // All the public functions will be available to all controllers that imports'."\n".'    // this model.  '."\n".'    //'."\n".'}';

            if (!file_exists(MODELDIR.$model_file.'.php')) {
                $filesystem->saveFile(MODELDIR.$model_file.'.php', $model);
                self::printText('Model created successfully at '.MODELDIR, 'green');

                return true;
            } else {
                self::printText(MODELDIR.$model_file.'.php file exists.', 'red');

                return false;
            }
        } elseif ($type == 'controller' && !empty($name)) {
            // Create controller
            $controller_file  = Loader::fromCamelCase($name);
            $controller_class = Loader::toCamelCase($name);
            $controller       = '<?php'."\n\n".'namespace Advandz\App\Controller;'."\n\n".'class '.$controller_class.' extends AppController'."\n".'{'."\n".'    //'."\n".'    // TODO: Define any methods, load any models or components or anything else'."\n".'    // in the preAction method, that you would like to be available to all methods '."\n".'    // of this controller.'."\n".'    //'."\n".'}';

            if (!file_exists(CONTROLLERDIR.$controller_file.'.php')) {
                $filesystem->saveFile(CONTROLLERDIR.$controller_file.'.php', $controller);
                self::printText('Controller created successfully at '.CONTROLLERDIR, 'green');

                return true;
            } else {
                self::printText(CONTROLLERDIR.$controller_file.'.php file exists.', 'red');

                return false;
            }
        } elseif ($type == 'facade' && !empty($name)) {
            // Create facade
            $facade_file  = Loader::fromCamelCase($name);
            $facade_class = Loader::toCamelCase($name);
            $facade       = '<?php'."\n\n".'namespace Advandz\App\Facade;'."\n\n".'final class '.$facade_class.''."\n".'{'."\n".'    /**'."\n".'     * Protected constructor to prevent instance creation.'."\n".'     */'."\n".'    protected function __construct()'."\n".'    {'."\n".'        // Nothing to do'."\n".'    }'."\n".'}';

            if (!file_exists(FACADEDIR.$facade_file.'.php')) {
                $filesystem->saveFile(FACADEDIR.$facade_file.'.php', $facade);
                self::printText('Facade created successfully at '.FACADEDIR, 'green');

                return true;
            } else {
                self::printText(FACADEDIR.$facade_file.'.php file exists.', 'red');

                return false;
            }
        } else {
            self::printText('Usage:', 'brown');
            self::printText('create ["plugin"|"middleware"|"model"|"controller"|"facade"] [name]');

            return false;
        }
    }

    /**
     * Generates a new encryption key.
     *
     * @param string $size The size of the key
     * @return string The generated key
     */
    final public static function key($size = 16)
    {
        $encryption = new Encryption();

        self::printText('Key:', 'brown');
        self::printText($encryption->generateKey($size), 'green');

        return $encryption->generateKey($size);
    }

    final public static function app(...$controller)
    {
        $args = array_merge(['cli'], $controller);

        ob_start();
        Dispatcher::dispatchCli($args);
        $output = ob_get_contents();
        ob_end_clean();

        self::printText($output);

        return $output;
    }

    /**
     * Prints the help message.
     */
    final public static function help()
    {
        self::printText('Usage:', 'brown');
        self::printText('command :[action] [parameters]');

        self::printText("\n".'Available commands:', 'brown');
        self::printText("\033[32m".'server'."\033[0m".'             - Starts a development web server.');
        self::printText("\033[33m".'   :[port]'."\033[0m".'         - The port number to start the server.');

        self::printText("\033[32m".'inspire'."\033[0m".'            - Show an inspiring quote.');

        self::printText("\033[32m".'app'."\033[0m".'                - Execute the app.');
        self::printText("\033[33m".'   :[controller]'."\033[0m".'   - The controller to execute.');

        self::printText("\033[32m".'cache'."\033[0m".'              - Rebuild or flush the cache.');
        self::printText("\033[33m".'   :flush'."\033[0m".'          - Flush the saved cache.');
        self::printText("\033[33m".'   :rebuild'."\033[0m".'        - Rebuilds the cache.');

        self::printText("\033[32m".'create'."\033[0m".'             - Create a new class.');
        self::printText("\033[33m".'   :plugin'."\033[0m".'         - Create a new plugin.');
        self::printText("\033[33m".'   :middleware'."\033[0m".'     - Create a new middleware.');
        self::printText("\033[33m".'   :model'."\033[0m".'          - Create a new model.');
        self::printText("\033[33m".'   :controller'."\033[0m".'     - Create a new controller.');
        self::printText("\033[33m".'   :facade'."\033[0m".'         - Create a new facade.');

        self::printText("\033[32m".'key'."\033[0m".'                - Generates a new encryption key.');
        self::printText("\033[33m".'   :[size]'."\033[0m".'         - The size of the key. (Default = 16)');

        self::printText("\033[32m".'help'."\033[0m".'               - Shows this message.');
    }

    /**
     * Escapes and sanitizes an argument.
     * 
     * @param  string $argument The argument to clean
     * @return string The clean argument
     */
    final private static function safeArgument($argument)
    {
        return escapeshellarg($argument);
    }

    /**
     * Prints a text with color.
     * 
     * @param  string $text  The text to print
     * @param  string $color The color of the text
     */
    final private static function printText($text, $color = 'default')
    {
        $colors = [
            'black'  => 30,
            'blue'   => 34,
            'green'  => 32,
            'cyan'   => 36,
            'red'    => 31,
            'purple' => 35,
            'brown'  => 33,
            'gray'   => 37
        ];

        if (substr(php_sapi_name(), 0, 3) == 'cli') {
            if (array_key_exists($color, $colors)) {
                print "\033[".$colors[$color].'m'.$text."\033[0m \n";
            } else {
                print $text."\n";
            }
        }
    }
}
