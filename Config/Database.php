<?php

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $host = 'localhost';
            $dbname = 'crowdfundingDb';
            $username = 'root';
            $password = '';

            $this->connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            /* This line of code is setting the error handling mode for the PDO (PHP Data Objects)
            connection. Specifically, it is setting the error mode to `PDO::ERRMODE_EXCEPTION`. */
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            /* This line of code is setting the default fetch mode for the PDO (PHP Data Objects)
            connection. In this case, it is setting the default fetch mode to `PDO::FETCH_ASSOC`,
            which means that when fetching results from a database query, each row will be returned
            as an associative array where the keys are the column names. This can make it easier to
            work with the fetched data as you can directly access columns by their names. */
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection error : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
