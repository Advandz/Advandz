<?php
/**
 * Provides helper methods that can assist you while you build your command-line application.
 *
 * @package Advandz
 * @subpackage Advandz.helpers.cli
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Helper;

class Cli
{
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
     * Prints a colored text in the CLI output.
     *
     * @param string $text  The text to print
     * @param string $color The color of the text
     */
    public function printText($text, $color = 'default')
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

        if ($this->isCli()) {
            if (array_key_exists($color, $colors)) {
                echo "\033[" . $colors[$color] . 'm' . $text . "\033[0m \n";
            } else {
                echo $text . "\n";
            }
        }
    }

    /**
     * Executes a command.
     *
     * @param string $command The command to execute
     * @param bool   $bypass  True to send the raw outuput directly to the output buffer
     */
    public function executeCommand($command, $bypass = false, &$output = null)
    {
        if ($bypass) {
            passthru($command, $output);
        } else {
            exec($command, $output);
        }
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
