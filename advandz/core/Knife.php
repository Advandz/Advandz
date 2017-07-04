<?php
/**
 * A simple and powerful templating engine that does not restrict you from using PHP code in your views.
 * In fact, all views are compiled into PHP code.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://www.advandz.com/eula/ The Advandz License Agreement
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Core;

use Exception;
use Advandz\Helper\Html;

class Knife extends Language
{
    /**
     * @var string The template code
     */
    public $template;

    /**
     * @var array The tags with the equivalent PHP code
     */
    private $tags = [
        '@lang'      => '<?php echo $this->Html->safe($this->_("%%STATEMENT%%", true)); ?>',
        '!@lang'     => '<?php $this->_("%%STATEMENT%%"); ?>',
        '@yield'     => '<?php echo $this->Html->safe($this->%%STATEMENT%%); ?>',
        '!@yield'    => '<?php echo $this->%%STATEMENT%%; ?>',
        '@raw'       => '<?php echo $this->Html->safe(print_r(%%STATEMENT%%, true)); ?>',
        '!@raw'      => '<?php print_r(%%STATEMENT%%); ?>',
        '@this'      => '<?php $this->%%STATEMENT%%; ?>',
        '!@this'     => '<?php $this->%%STATEMENT%%; ?>',
        '@constant'  => '<?php echo $this->Html->safe(defined(%%STATEMENT%%) ? %%STATEMENT%% : \'%%STATEMENT%%\'); ?>',
        '!@constant' => '<?php echo (defined(%%STATEMENT%%) ? %%STATEMENT%% : \'%%STATEMENT%%\'); ?>',
        '@include'   => '<?php include %%STATEMENT%%; ?>',
        '!@include'  => '<?php include_once %%STATEMENT%%; ?>',
        '@require'   => '<?php require %%STATEMENT%%; ?>',
        '!@require'  => '<?php require_once %%STATEMENT%%; ?>',
        '@print'     => '<?php echo $this->Html->safe(%%STATEMENT%%); ?>',
        '!@print'    => '<?php echo %%STATEMENT%%; ?>',
        'if'         => '<?php if (%%STATEMENT%%) { ?>',
        'elseif'     => '<?php } elseif (%%STATEMENT%%) { ?>',
        'else'       => '<?php } else { ?>',
        '/if'        => '<?php } ?>',
        'while'      => '<?php while (%%STATEMENT%%) { ?>',
        '/while'     => '<?php } ?>',
        'do'         => '<?php do { ?>',
        'dowhile'    => '<?php } while (%%STATEMENT%%); ',
        '/do'        => '?>',
        'for'        => '<?php for (%%STATEMENT%%) { ?>',
        '/for'       => '<?php } ?>',
        'foreach'    => '<?php foreach (%%STATEMENT%%) { ?>',
        '/foreach'   => '<?php } ?>',
        'php'        => '<?php ',
        '/php'       => '?>'
    ];

    /**
     * Compiles the template into PHP code.
     *
     * @param  string    $file The template file used as our view
     * @throws Exception When is not a valid view or you don't have the permissions to read them
     * @return string    The file location to the compiled code
     */
    public function compile($file = null)
    {
        if (Configure::get('Knife.on')) {
            // Initialize HTML Helper
            $this->Html = new Html();

            // Set compiled view file
            $compiled_file = CACHEDIR . md5($file) . '.php';

            // Delete compiled view if is old
            if (Configure::get('Caching.on') && file_exists($compiled_file)) {
                $build_date = filemtime($compiled_file); // Last modified date of the compiled view
                $ttl        = (!empty(Configure::get('Caching.ttl')) ? Configure::get('Caching.ttl') : 3600);

                if ($build_date + $ttl <= time()) {
                    unlink($compiled_file);
                }
            }

            // Create compiled view
            if (!file_exists($compiled_file)) {
                // Check if the view exists and get the content
                if (file_exists($file)) {
                    $this->template = file_get_contents($file);

                    // Remove PHP tags
                    $this->replaceTemplate('<?php', '&lt;?php');
                    $this->replaceTemplate('?>', '?&gt;');
                }

                if (!$this->template) {
                    throw new Exception("File is not a valid view or you don't have the permissions to read them: " . $file);
                }

                // Compile tags
                $this->compileTags();

                // Save compiled view
                file_put_contents($compiled_file, $this->template);
            }

            // Halt compiler
            if (!Configure::get('Caching.on')) {
                register_shutdown_function(
                    function () use ($compiled_file) {
                        unlink($compiled_file);
                    }
                );
            }

            return $compiled_file;
        } else {
            return $file;
        }
    }

    /**
     * Parse all the tags and convert them into PHP code.
     *
     * @throws Exception When a invalid tag is parsed
     */
    private function compileTags()
    {
        // Parse tags
        $this->replaceTemplate('{{--', '<?php /*');
        $this->replaceTemplate('--}}', '*/ ?>');
        $this->replaceTemplate('\{', '/\x7b');
        $this->replaceTemplate('\}', '/\x7d');

        // Compile the tags
        preg_replace_callback('/\\{\\{([^{}]+)\\}\\}/', function ($matches) {
            $args = explode(' ', trim($matches[1]), 2);

            if (array_key_exists($args[0], $this->tags)) {
                @$this->replaceTag($matches[0], $this->tags[$args[0]], $args[1]);
            } else {
                throw new Exception($matches[0] . ' is a invalid tag');
            }
        }, $this->template);

        // Replace escaped tags
        $this->replaceTemplate('/\x7b', '{');
        $this->replaceTemplate('/\x7d', '}');
    }

    /**
     * Replace a tag in the template for a PHP code.
     *
     * @param string $tag       The tag to be replaced
     * @param string $code      The PHP code
     * @param string $statement The called function arguments
     */
    private function replaceTag($tag, $code, $statement)
    {
        $code = str_replace('%%STATEMENT%%', $statement, $code);
        $this->replaceTemplate($tag, $code);
    }

    /**
     * Replace a string in the template code.
     *
     * @param mixed $search  The value being searched for. An array may be used to designate multiple needles.
     * @param mixed $replace The replacement value that replaces found search values. An array may be used to
     *                       designate multiple replacements.
     */
    private function replaceTemplate($search, $replace)
    {
        $this->template = str_replace($search, $replace, $this->template);
    }
}
