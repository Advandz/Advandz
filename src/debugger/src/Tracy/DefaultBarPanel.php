<?php
/**
 * PHP debugging tool, It is an ultimate tool among the diagnostic ones.
 *
 * @package Advandz
 * @subpackage Advandz.debugger
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
namespace Tracy;

/**
 * IBarPanel implementation helper.
 *
 * @internal
 */
class DefaultBarPanel implements IBarPanel {
	private $id;
	public $data;

	public function __construct($id) {
		$this->id = $id;
	}

	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 */
	public function getTab() {
		ob_start(function () {
		});
		$data = $this->data;
		require __DIR__ . "/assets/Bar/{$this->id}.tab.phtml";

		return ob_get_clean();
	}

	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 */
	public function getPanel() {
		ob_start(function () {
		});
		if (is_file(__DIR__ . "/assets/Bar/{$this->id}.panel.phtml")) {
			$data = $this->data;
			require __DIR__ . "/assets/Bar/{$this->id}.panel.phtml";
		}

		return ob_get_clean();
	}
}
?>