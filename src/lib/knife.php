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
	 * @throws Exception
	 */
	public final function compile($file = null) {
		if (!file_exists($file))
			throw new Exception("Files does not exist: " . $file);

		$this->template = file_get_contents($file);

		if (!$this->template)
			throw new Exception("File is not a valid view: " . $file);

		// Parse tags
		$this->parse();

		// Execute compiled code
		eval($this->template);
	}

	/**
	 * Parse all the tags and convert them into PHP code
	 */
	private function parse() {
		// Clean PHP tags
		$this->clean();

		// Parse template tags
		$this->parseTags();

		// Prepare template to be compiled
		$this->template = '?>' . $this->template . '<?php';
	}

	/**
	 * Parse the view template tags
	 */
	private function parseTags() {
		preg_replace_callback('/\\{\\{([^{}]+)\}\\}/', function ($matches) {
			// Get the tag arguments
			$args = explode(" ", $matches[1], 2);

			// Parse the "lang" tags
			if ($args[0] == "lang")
				$this->replaceTag($matches[0], Language::getText($args[1]));

			// Parse the "@" tags
			if ($args[0] == "@")
				$this->replaceTag($matches[0], '<?php echo $this->Html->safe($this->' . $args[1] . '); ?>');
		}, $this->template);

		// Parse the variable tags
		// With XSS filtering
		$this->replaceTag("{[\"", '<?php echo $this->Html->safe($');
		$this->replaceTag("\"]}", '); ?>');

		// Without XSS filtering
		$this->replaceTag("{![\"", '<?php echo $this->Html->ifSet($');

		// Parse the PHP code tags
		$this->replaceTag("{{php}}", '<?php ');
		$this->replaceTag("{{/php}}", ' ?>');
		$this->replaceTag("{{", '<?php ');
		$this->replaceTag("}}", ' ?>');

		// Replace escaped tags
		$this->replaceTag("{\{", '{{');
		$this->replaceTag("}\}", '}}');
	}

	/**
	 * Clean the template from PHP tags
	 */
	private function clean() {
		$this->replaceTag("<?", "&lt;?");
		$this->replaceTag("?>", "?&gt;");
		$this->replaceTag("<%", "&lt;%");
		$this->replaceTag("%>", "%&gt;");
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