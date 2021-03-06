<?php
/**
 * This class establishes and maintains a connection to a PDO resource, and
 * provides methods for interacting with that resource.
 *
 * @package Advandz
 * @subpackage Advandz.core
 * @copyright Copyright (c) 2010-2013 Phillips Data, Inc. All Rights Reserved.
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Cody Phillips <therealclphillips.woop@gmail.com>
 */

namespace Advandz\Core;

use PDO;
use Exception;

class Model
{
    /**
     * @var object PDO connection
     */
    private $connection;

    /**
     * @var array An array of all database connections established
     */
    private static $connections = [];

    /**
     * @var array An array of all database connection info (used to find a matching connection)
     */
    private static $db_infos = [];

    /**
     * @var object PDO Statement
     */
    private $statement;

    /**
     * @var mixed Fetch Mode the PDO:FETCH_* constant (int) to fetch records by, null to use default setting
     */
    private $fetch_mode = null;

    /**
     * @var array Default PDO attribute settings
     */
    private $default_pdo_options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_CASE               => PDO::CASE_LOWER,
        PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
        PDO::ATTR_PERSISTENT         => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SQL_MODE="ALLOW_INVALID_DATES";'
    ];

    /**
     * Creates a new Model object that establishes a new PDO connection using
     * the given database info, or the default configured info set in the database
     * config file if no info is given.
     *
     * @param array $db_info Database information for this connection
     */
    public function __construct($db_info = null)
    {
        // Load the database configuration
        Configure::load('database');

        $this->fetch_mode = Configure::get('Database.fetch_mode');

        // Only connect now if lazy connecting is disabled or if db info was given
        if (!Configure::get('Database.lazy_connecting') || $db_info !== null) {
            $this->makeConnection($db_info);
        }
    }

    /**
     * Sets the fetch mode to the given value, returning the old value.
     *
     * @param  mixed $fetch_mode The PDO:FETCH_* constant (int) to fetch records by, null to use default setting
     * @return mixed The fetch mode
     */
    public function setFetchMode($fetch_mode)
    {
        $cur              = $this->fetch_mode;
        $this->fetch_mode = $fetch_mode;

        return $cur;
    }

    /**
     * Get the last inserted ID.
     *
     * @param  string    $name The name of the sequence object from which the ID should be returned
     * @throws Exception Thrown when no PDO connection has been established
     * @return string    The last ID inserted, if available
     */
    public function lastInsertId($name = null)
    {
        if (!($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::lastInsertId when connection has not been instantiated');
        }

        return $this->connection->lastInsertId($name);
    }

    /**
     * Sets the given value to the given attribute for this connection.
     *
     * @param  string    $attribute The attribute to set
     * @param  int       $value     The value to assign to the attribute
     * @throws Exception Thrown when no PDO connection has been established
     */
    public function setAttribute($attribute, $value)
    {
        if (!($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::setAttribute when connection has not been instantiated');
        }

        $this->connection->setAttribute($attribute, $value);
    }

    /**
     * Query the Database using the given prepared statement and argument list.
     *
     * @param  string       $sql The SQL to execute
     * @throws Exception    Thrown when no PDO connection has been established
     * @return PDOStatement The resulting PDOStatement from the execution of this query
     */
    public function query($sql)
    {
        $params = func_get_args();
        array_shift($params); // Shift the SQL parameter off of the list

        // If 2nd param is an array, use it as the series of params, rather than
        // the rest of the param list
        if (isset($params[0]) && is_array($params[0])) {
            $params = $params[0];
        }

        // Ensure PDO connection exists
        if ($this->lazyConnect() && !($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::query when connection has not been instantiated');
        }

        // Store this statement in our PDO object for easy use later
        $this->statement = $this->prepare($sql, $this->fetch_mode);

        // Execute the query
        $this->statement->execute($params);

        // Return the statement
        return $this->statement;
    }

    /**
     * Prepares an SQL statement to be executed by the PDOStatement::execute() method.
     * Useful when executing the same query with different bound parameters.
     *
     * @param  string       $sql        The SQL statement to prepare
     * @param  int          $fetch_mode The PDO::FETCH_* constant, defaults to "Database.fetch_mode" config setting
     * @throws Exception    When connection has not been instantiated
     * @return PDOStatement The resulting PDOStatement from the preparation of this query
     * @see PDOStatement::execute()
     */
    public function prepare($sql, $fetch_mode = null)
    {
        // Ensure PDO connection exists
        if ($this->lazyConnect() && !($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::prepare when connection has not been instantiated');
        }

        if ($fetch_mode === null) {
            $fetch_mode = $this->fetch_mode;
        }

        $this->statement = $this->connection->prepare($sql);
        // Set the default fetch mode for this query
        $this->statement->setFetchMode($fetch_mode);

        return $this->statement;
    }

    /**
     * Begin a transaction.
     *
     * @throws Exception When connection has not been instantiated
     * @return bool      True if the transaction was successfully opened, false otherwise
     */
    public function begin()
    {
        // Ensure PDO connection exists
        if ($this->lazyConnect() && !($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::begin when connection has not been instantiated');
        }

        return $this->connection->beginTransaction();
    }

    /**
     * Rolls back and closes the transaction.
     *
     * @throws Exception When connection has not been instantiated
     * @return bool      True if the transaction was successfully rolled back and closed, false otherwise
     */
    public function rollBack()
    {
        // Ensure PDO connection exists
        if ($this->lazyConnect() && !($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::rollBack when connection has not been instantiated');
        }

        return $this->connection->rollBack();
    }

    /**
     * Commits a transaction.
     *
     * @throws Exception When connection has not been instantiated
     * @return bool      True if the transaction was successfully commited and closed, false otherwise
     */
    public function commit()
    {
        // Ensure PDO connection exists
        if ($this->lazyConnect() && !($this->connection instanceof PDO)) {
            throw new Exception('Call to Model::commit when connection has not been instantiated');
        }

        return $this->connection->commit();
    }

    /**
     * Returns the connection's PDO object if a connection has been established, null otherwise.
     *
     * @return PDO The PDO connection object, null if no connection exists
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get the number of rows affected by the last query.
     *
     * @param  PDOStatement $statement The statement to count affected rows on,
     *                                 if null the last Model::query() statement will be used.
     * @throws Exception    Thrown when called prior to Model::query()
     * @return int          The number of rows affected by the previous query
     */
    public function affectedRows($statement = null)
    {
        if ($statement == null) {
            $statement = $this->statement;
        }

        if (!($statement instanceof PDOStatement)) {
            throw new Exception('Call to Model::affectedRows before initializing a statement, call Model::query first');
        }

        return $statement->rowCount();
    }

    /**
     * Build a DSN string using the given array of parameters.
     *
     * @param  array     $db_params An array of parameters
     * @throws Exception Thrown when $db contains invalid parameters
     * @return string    The DSN string
     */
    public static function makeDSN($db_params)
    {
        if (!isset($db_params['driver']) || !isset($db_params['database']) || !isset($db_params['host'])) {
            throw new Exception("Call to Model::makeDSN with invalid parameters, required an Array like ['driver'=>,'database'=>,'host'=>]");
        }

        return $db_params['driver'] . ':dbname=' . $db_params['database'] . ';host=' . $db_params['host'] . (isset($db_params['port']) ? ';port=' . $db_params['port'] : '');
    }

    /**
     * Establish a new PDO connection using the given array of information. If
     * a connection already exists, no new connection will be created.
     *
     * @param  array     $db_info Database information for this connection
     * @throws Exception Throw when PDOException is encountered
     */
    private function makeConnection($db_info = null)
    {
        if ($db_info === null) {
            $db_info = Configure::get('Database.profile');
        }

        // Attempt to reuse an existing connection if one exists that matches this connection
        if (Configure::get('Database.reuse_connection') !== false && ($key = array_search($db_info, self::$db_infos)) !== false) {
            $this->connection =&self::$connections[$key];
        }

        // Only attempt to set up a new connection if none exists
        if (!($this->connection instanceof PDO)) {
            // Override any default settings with those provided
            $options = (array) (isset($db_info['options']) ? $db_info['options'] : null) + $this->default_pdo_options;
            // Ensure persistence is set to either true or false
            $options[PDO::ATTR_PERSISTENT] = (isset($db_info['persistent']) ? $db_info['persistent'] : false);

            try {
                $this->connection = new PDO(self::makeDSN($db_info), (isset($db_info['user']) ? $db_info['user'] : null), (isset($db_info['pass']) ? $db_info['pass'] : null), $options);

                // Record the connection
                self::$connections[] =&$this->connection;
                self::$db_infos[]    = $db_info;

                // Run a character set query to override the database server's default character set
                if (isset($db_info['charset_query']) && $db_info['charset_query'] != '') {
                    $this->query($db_info['charset_query']);
                }
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    /**
     * Attempt to connect to the database if lazy connecting is enabled and no
     * connection yet exists.
     */
    private function lazyConnect()
    {
        if (Configure::get('Database.lazy_connecting') && !($this->connection instanceof PDO)) {
            $this->makeConnection();
        }
    }
}
