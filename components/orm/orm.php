<?php
/**
 * Provides a number of methods for manage your database with a Object-relational mapping
 * that lets you query and manipulate data from a database using an object-oriented paradigm.
 *
 * @package Advandz
 * @subpackage Advandz.components.orm
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

class Orm extends Record
{
    /**
     * Initializes a Table Class.
     *
     * @param  string    $table The database table to use
     * @return Table     Returns a Table Object if the table exists
     * @throws Exception If the table not exists in the database
     */
    public function _($table)
    {
        // Load the table class
        \Loader::load(COMPONENTDIR.'orm'.DS.'table.php');

        // Convert table name
        $table = \Loader::fromCamelCase($table);

        // Check if table exists
        if ($this->select()->from($table)->numResults() > 0) {
            // Initialize the table class
            $table_cc          = \Loader::toCamelCase($table);
            $this->{$table_cc} = new Table($table);

            return $this->{$table_cc};
        } else {
            throw new \Exception('Table "'.$table."\" doesn't exist");
        }
    }
}
