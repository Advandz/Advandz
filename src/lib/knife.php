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

				// Parse tags
				$this->parseTags();

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
	 */
	private function parseTags() {
		preg_replace_callback('/\\{\\{([^{}]+)\}\\}/', function ($matches) {
			// Get the tags arguments
			$args = explode(" ", trim($matches[1]), 2);

			// Parse the "lang" tags
			if ($args[0] == "lang")
				$this->replaceTag($matches[0], Language::getText($args[1]));

			// Parse the "include" tags
			if ($args[0] == "include")
				$this->replaceTag($matches[0], "<?php include " . $args[1] . "; ?>");

			// Parse the "@include" tags
			if ($args[0] == "@include")
				$this->replaceTag($matches[0], "<?php include_once " . $args[1] . "; ?>");

			// Parse the "require" tags
			if ($args[0] == "require")
				$this->replaceTag($matches[0], "<?php require " . $args[1] . "; ?>");

			// Parse the "@require" tags
			if ($args[0] == "@require")
				$this->replaceTag($matches[0], "<?php require_once " . $args[1] . "; ?>");

			// Parse the "if" tags
			if ($args[0] == "if")
				$this->replaceTag($matches[0], "<?php if (" . $args[1] . ") { ?>");
			if ($args[0] == "elseif")
				$this->replaceTag($matches[0], "<?php } elseif (" . $args[1] . ") { ?>");

			// Parse the "while" tags
			if ($args[0] == "while")
				$this->replaceTag($matches[0], "<?php while (" . $args[1] . ") { ?>");

			// Parse the "dowhile" tags
			if ($args[0] == "dowhile")
				$this->replaceTag($matches[0], "<?php } while (" . $args[1] . ");");

			// Parse the "for" tags
			if ($args[0] == "for")
				$this->replaceTag($matches[0], "<?php for (" . $args[1] . ") { ?>");

			// Parse the "foreach" tags
			if ($args[0] == "foreach")
				$this->replaceTag($matches[0], "<?php foreach (" . $args[1] . ") { ?>");
		}, $this->template);

		// Parse structure tags
		$this->replaceTag("{{else}}", '<?php } else { ?>');
		$this->replaceTag("{{do}}", '<?php do { ?>');
		$this->replaceTag("{{/if}}", '<?php } ?>');
		$this->replaceTag("{{/do}}", '?>');
		$this->replaceTag("{{/while}}", '<?php } ?>');
		$this->replaceTag("{{/for}}", '<?php } ?>');

		// Parse yield variables tags
		$this->replaceTag("{[@yield ", '<?php echo $this->Html->safe($this->');
		$this->replaceTag("{![@yield ", '<?php echo ($this->'); // Without XSS filtering

		// Parse RAW variables tags
		$this->replaceTag("{[@raw ", '<?php print_r(');
		$this->replaceTag("{![@raw ", '<?php print_r('); // Without XSS filtering

		// Parse variables tags
		$this->replaceTag("{[@var ", '<?php echo $this->Html->safe($');
		$this->replaceTag("{![@var ", '<?php echo ($'); // Without XSS filtering

		$this->replaceTag("]}", '); ?>');

		// Parse PHP tags
		$this->replaceTag("{{", '<?php ');
		$this->replaceTag("}}", ' ?>');

		// Replace escaped tag
		$this->replaceTag("{\{", '{{');
		$this->replaceTag("}\}", '}}');
		$this->replaceTag("{\\{", '{\{');
		$this->replaceTag("}\\}", '}\}');
	}

	/**
	 * Replace a tag in the template for a PHP code
	 *
	 * @param string $tag The tag to be replaced
	 * @param string $code The PHP code
	 */
	private function replaceTag($tag, $code) {
		$this->template = str_replace($tag, $code, $this->template);
	}
}
?>
