<?php
/**
 * Provides helper methods that can assist you while you build your command-line application.
 *
 * @package Advandz
 * @subpackage Advandz.helpers.cli
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Helper;

class Cli
{
    /**
     * @var string An string containing the text output buffer
     */
    private $text;

    /**
     * Creates a new Table object.
     * @param mixed $table
     */
    public function __construct($table)
    {
        if (substr(php_sapi_name(), 0, 3) != 'cli') {
            throw new Exception('Cli::class is not supported when running php-cgi');
        }
    }

    /**
     * Check if the call is from the CLI.
     *
     * @return bool True if the call is from the CLI
     */
    public function isCli()
    {
        return substr(php_sapi_name(), 0, 3) == 'cli';
    }

    /**
     * Colour a text in the CLI output.
     *
     * @param  string $text   The text to print
     * @param  string $color  The color of the text
     * @return Cli    An instance of Cli
     */
    public function color($text, $color = 'default')
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

        if (array_key_exists($color, $colors)) {
            $this->text .= "\033[" . $colors[$color] . 'm' . $text . "\033[0m";
        } else {
            $this->text .= $text;
        }

        return $this;
    }

    /**
     * Prints a colored text in the CLI output.
     *
     * @param string $text The text to print, if not provided the stored text will be printed
     */
    public function print($text = null)
    {
        if (empty($text)) {
            $text = $this->text;
        }

        echo $text . "\n";
    }

    /**
     * Executes a command.
     *
     * @param string $command The command to execute
     * @param bool   $bypass  True to send the raw outuput directly to the output buffer
     * @param mixed  The result of the executed command
     */
    public function executeCommand($command, $bypass = false)
    {
        $output = '';

        if ($bypass) {
            passthru($command, $output);
        } else {
            exec($command, $output);
        }

        return $output;
    }

    /**
     * Clean and make safe an argument.
     *
     * @param  string $argument The argument to clean
     * @return string The clean argument
     */
    public function safe($argument)
    {
        return escapeshellarg($argument);
    }
}
