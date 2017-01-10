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
class Table
{
    /**
     * @var string The current table name
     */
    private $table = null;

    public function __construct($table)
    {
        Loader::loadComponents($this, ['Record']);
        $this->table = $table;
    }

    public function add()
    {
        //
        // TODO: Create add function
        // This function allow add a new entry in the table.
        //
    }

    public function read()
    {
        //
        // TODO: Create read function
        // This function allow read a existent entry in the table.
        //
    }

    public function edit()
    {
        //
        // TODO: Create edit function
        // This function allow edit a existent entry in the table.
        //
    }

    public function remove()
    {
        //
        // TODO: Create remove function
        // This function allow remove a existent entry in the table.
        //
    }
}
