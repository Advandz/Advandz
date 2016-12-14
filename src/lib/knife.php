<?php
/**
 * A simple and powerful templating engine that does not restrict you from using PHP code in your views.
 * In fact, all views are compiled into PHP code.
 *
 * @package Advandz
 * @subpackage Advandz.lib
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
class Knife extends Language {
	/**
	 * @var string The template code
	 */
	public $template;
	/**
	 * @var array The tags with the equivalent PHP code
	 */
	private $tags = [
		'@lang'     => "<? echo \$this->Html->safe(\$this->_(\"%%STATEMENT%%\", true)); ?>",
		'!@lang'    => "<? \$this->_(\"%%STATEMENT%%\"); ?>",
		'@yield'    => "<? echo \$this->Html->safe(\$this->%%STATEMENT%%); ?>",
		'!@yield'   => "<? echo \$this->%%STATEMENT%%; ?>",
		'@raw'      => "<? echo \$this->Html->safe(print_r(\$%%STATEMENT%%, true)); ?>",
		'!@raw'     => "<? print_r(\$%%STATEMENT%%); ?>",
		'@var'      => "<? echo \$this->Html->safe(\$%%STATEMENT%%); ?>",
		'!@var'     => "<? echo \$%%STATEMENT%%; ?>",
		'@this'     => "<? \$this->",
		'!@this'    => "<? \$this->",
		'@constant' => "<? echo (defined(%%STATEMENT%%) ? %%STATEMENT%% : '%%STATEMENT%%'); ?>",
		'include'   => "<? include %%STATEMENT%%; ?>",
		'@include'  => "<? include_once %%STATEMENT%%; ?>",
		'require'   => "<? require %%STATEMENT%%; ?>",
		'@require'  => "<? require_once %%STATEMENT%%; ?>",
		'if'        => "<? if (%%STATEMENT%%) { ?>",
		'elseif'    => "<? } elseif (%%STATEMENT%%) { ?>",
		'else'      => "<? } else { ?>",
		'/if'       => "<? } ?>",
		'while'     => "<? while (%%STATEMENT%%) { ?>",
		'/while'    => "<? } ?>",
		'do'        => "<? do { ?>",
		'dowhile'   => "<? } while (%%STATEMENT%%); ",
		'/do'       => "?>",
		'for'       => "<? for (%%STATEMENT%%) { ?>",
		'/for'      => "<? } ?>",
		'foreach'   => "<? foreach (%%STATEMENT%%) { ?>",
		'/foreach'  => "<? } ?>",
		'php'       => "<?php ",
		'/php'      => " ?>"
	];

	/**
	 * Compiles the template into PHP code
	 *
	 * @param string $file The template file used as our view
	 * @return string The file location to the compiled code
	 * @throws Exception
	 */
	public final function compile($file = null) {
		// Load HTML helper
		Loader::loadHelpers($this, ['Html']);

		if (Configure::get("Knife.on")) {
			// Set compiled view file
			$compiled_file = CACHEDIR . md5($file) . '.php';

			// Delete compiled view if is old
			if (Configure::get("Caching.on") && file_exists($compiled_file)) {
				$build_date = filemtime($compiled_file); // Last modified date of the compiled view
				$cache_time = (!empty(Configure::get("Knife.cache_time")) ? Configure::get("Knife.cache_time") : 3600);

				if ($build_date + $cache_time <= time())
					unlink($compiled_file);
			}

			// Create compiled view
			if (!file_exists($compiled_file)) {
				// Check if the view exists and get the content
				if (file_exists($file))
					$this->template = file_get_contents($file);

				if (!$this->template)
					throw new Exception("File is not a valid view or you don't have the permissions to read them: " . $file);

				// Compile tags
				$this->compileTags();

				// Save compiled view
				file_put_contents($compiled_file, $this->template);
			}

			// Halt compiler
			if (!Configure::get("Caching.on")) {
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
	 * Parse all the tags and convert them into PHP code
	 *
	 * @throws Exception
	 */
	private function compileTags() {
		// Parse comment tags
		$this->replaceTemplate("{{--", '<? /*');
		$this->replaceTemplate("--}}", '*/ ?>');

		// Compile the tags
		preg_replace_callback('/\\{\\{([^{}]+)\}\\}/', function ($matches) {
			$args = explode(" ", trim($matches[1]), 2);

			if (array_key_exists($args[0], $this->tags)) {
				$this->replaceTag($matches[0], $this->tags[$args[0]], $args[1]);
			} else {
				throw new Exception($matches[0] . " is a invalid tag");
			}
		}, $this->template);

		// Replace escaped tags
		$this->replaceTemplate('{\{', '{{');
		$this->replaceTemplate('}\}', '}}');
		$this->replaceTemplate('{\\{', '{\{');
		$this->replaceTemplate('}\\}', '}\}');
	}

	/**
	 * Replace a tag in the template for a PHP code
	 *
	 * @param string $tag The tag to be replaced
	 * @param string $code The PHP code
	 * @param string $statement The called function arguments
	 */
	private function replaceTag($tag, $code, $statement) {
		$code = $this->replace('%%STATEMENT%%', $statement, $code);
		$this->replaceTemplate($tag, $code);
	}

	/**
	 * Replace a string in the template code
	 *
	 * @param mixed $search The value being searched for. An array may be used to designate multiple needles.
	 * @param mixed $replace The replacement value that replaces found search values. An array may be used to
	 *     designate multiple replacements.
	 */
	private function replaceTemplate($search, $replace) {
		$this->template = $this->replace($search, $replace, $this->template);
	}

	/**
	 * Replace a value in the given data
	 *
	 * @param mixed $search The value being searched for. An array may be used to designate multiple needles.
	 * @param mixed $replace The replacement value that replaces found search values. An array may be used to
	 *     designate multiple replacements.
	 * @param mixed $data The string or array being searched and replaced on.
	 * @return mixed Returns a string with all occurrences of search in data replaced with the given replace value.
	 */
	private function replace($search, $replace, $data) {
		return str_replace($search, $replace, $data);
	}
}
?>