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

class Table extends Record
{
    /**
     * @var string The current table name
     */
    private $table = null;

    /**
     * Creates a new Table object.
     */
    public function __construct($table)
    {
        parent::__construct();
        $this->table = $table;
    }

    /**
     * Catch-all calls, Get a entry from the database based on the called method.
     *
     * @param  string $method_name The called method
     * @param  array  $args        An array containing the method arguments
     * @return mixed  The resultant entry from the database
     */
    public function __call($method_name, $args)
    {
        if (!method_exists($this, $method_name)) {
            if (!empty($args)) {
                $result = $this->get('*', [$this->table . '.' . $method_name, '=', $args[0]]);
            } else {
                $result = $this->get($method_name);
            }

            if (count($result) == 1) {
                $result = $result[0];
            }

            if (empty($result)) {
                return false;
            }

            return $result;
        }
    }

    /**
     * Add a entry in the table.
     *
     * @param  array $params An array containing the parameters to insert
     * @return Table An instance of Table
     */
    public function add($params)
    {
        return $this->insert($this->table, $params);
    }

    /**
     * Get a entry from the table.
     *
     * @param  array $params An array or string containing the parameters to fetch
     * @param  array $where  An array containing the where sentence
     * @return Table An instance of Table
     */
    public function get($params = '*', $where = [])
    {
        if (!empty($where)) {
            return $this->select($params)->from($this->table)->where($where[0], $where[1], $where[2])->fetchAll();
        }

        return $this->select($params)->from($this->table)->fetchAll();
    }

    /**
     * Edit a entry from the table.
     *
     * @param  array $params An array or string containing the parameters to update
     * @param  array $where  An array containing the where sentence
     * @return Table An instance of Table
     */
    public function edit($params, $where = [])
    {
        if (!empty($where)) {
            return $this->where($where[0], $where[1], $where[2])->update($this->table, $params);
        }

        return $this->update($this->table, $params);
    }

    /**
     * Remove a entry from the table.
     *
     * @param  array $where An array containing the where sentence
     * @return Table An instance of Table
     */
    public function remove($where = [])
    {
        if (!empty($where)) {
            return $this->from($this->table)->where($where[0], $where[1], $where[2])->delete();
        }

        return $this->from($this->table)->delete();
    }
}
