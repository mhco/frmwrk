<?php

namespace Src\Core;

use PDO;

class Database
{
    /**
     * @var null Database Connection
     */
    public $db = null;

    /**
     * Whenever model is created, open a database connection.
     */
    function __construct()
    {
        try
        {
            $this->db = $this->openDatabaseConnection();
        }
        catch (\PDOException $e)
        {
            echo "<p>Database connection could not be established.</p>";
            echo "<p>Currently running PHP " . phpversion() . " (should be 8.0+)</p>";
            echo version_compare(phpversion(), '8.0.0') < 0 ? "I found the problem... your PHP version isn't compatible. Please upgrade or select the proper version.<br>" : null;

            exit(1);
        }
    }

    /**
     * Open the database connection with the credentials from .env
     */
    private function openDatabaseConnection()
    {
        // set the (optional) options of the PDO connection. in this case, we set the fetch mode to
        // "objects", which means all results will be objects, like this: $result->user_name !
        // For example, fetch mode FETCH_ASSOC would return results like this: $result["user_name] !
        // @see http://www.php.net/manual/en/pdostatement.fetch.php
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
        ];

        // generate a database connection, using the PDO connector
        // @see http://net.tutsplus.com/tutorials/php/why-you-should-be-using-phps-pdo-for-database-access/
        $dsn = "{$_ENV['DB_TYPE']}:host={$_ENV['DB_HOST']}; dbname={$_ENV['DB_NAME']}; charset=utf8";

        return new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
    }
}
