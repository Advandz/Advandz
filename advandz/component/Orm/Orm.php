<?php
/**
 * Provides a number of methods for manage your database with a Object-relational mapping
 * that lets you query and manipulate data from a database using an object-oriented paradigm.
 *
 * @package Advandz
 * @subpackage Advandz.components.orm
 * @copyright Copyright (c) 2016-2017 Advandz, LLC. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author The Advandz Team <team@advandz.com>
 */

namespace Advandz\Component;

use Advandz\Helper\Text;
use Advandz\Component\Orm\Table;

class Orm extends Record
{
    /**
     * Initializes a Table Class.
     *
     * @param  mixed     $tables The database table to use
     * @throws Exception If the table not exists in the database
     * @return Table     Returns a Table Object if the table exists
     */
    public function _($tables)
    {
        // Load the necessary helpers
        $text = new Text();

        // Prepare tables array
        $tables = (array) $tables;

        // Initialize the table class
        foreach ($tables as $table) {
            // Check if table exists
            if ($this->select()->from($table)) {
                $table_cc          = $text->studlyCase($table);
                $this->{$table_cc} = new Table($table);

                return $this->{$table_cc};
            } else {
                throw new \Exception('Table "' . $table . "\" doesn't exist");
            }
        }
    }
}
