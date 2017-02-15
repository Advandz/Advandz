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

class Table extends Record
{
    /**
     * @var string The current table name
     */
    private $table = null;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function __call($method_name, $args) {
        if(!function_exists($method_name)) {
            if (!empty($args)) {
                return $this->get($method_name, [$method_name, '=', $args[0]])->fetchAll();
            }

            return $this->get($method_name)->fetchAll();
        }
    }

    public function add($params)
    {
        return $this->insert($this->table, $params);
    }

    public function get($params = null, $where = [])
    {
        if (empty($where)) {
            return $this->select($params)->from($this->table)->where($where[0], $where[1], $where[2]);
        }

        return $this->select($params)->from($this->table);
    }

    public function edit($params, $where = [])
    {
        if (!empty($where)) {
            return $this->where($where[0], $where[1], $where[2])->update($this->table, $params);
        }

        return $this->update($this->table, $params);
    }

    public function delete($where = [])
    {
        if (!empty($where)) {
            return $this->from($this->table)->where($where[0], $where[1], $where[2])->delete();
        }

        return $this->from($this->table)->delete();
    }
}
