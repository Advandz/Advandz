<?php
/**
 * Provides a number of methods for manage your database with a Object-relational mapping that lets you query and
 * manipulate data from a database using an object-oriented paradigm.
 *
 * @package Advandz
 * @subpackage Advandz.components.orm
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */
Loader::load(COMPONENTDIR . "orm" . DS . "table.php");

class Orm extends Record {
	/**
	 * Initializes a Table Class
	 *
	 * @param string $table Called function
	 * @return mixed Returns a Table Object if the table exists
	 */
	public function loadTable($table) {
		$table_exists = $this->select()->from(Loader::fromCamelCase($table))->numResults();
		if ($table_exists) {
			$this->{Loader::toCamelCase($table)} = new Table(Loader::fromCamelCase($table));

			return $this->{Loader::toCamelCase($table)};
		}

		return null;
	}
}
?>