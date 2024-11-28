<?php

class Db
{
    private static $instance = null;
    private $dbConnection = null;


    private function __construct()
    {
        define('HOSTNAME', "localhost");
        define('USERNAME', "root");
        define('PASSWORD', "");
        define('DATABASE', "job_application");

        // Connect to the database
        $this->dbConnection = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
        if ($this->dbConnection->connect_error) {
            die("Connection failed: " . $this->dbConnection->connect_error);
        }
    }

    // Singleton Pattern for DB
    public static function getInstance()
    {
        if (self::$instance === null) {
            // Assign the instance to the class
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }


    public function close()
    {
        if ($this->dbConnection) {
            mysqli_close($this->dbConnection);
            $this->dbConnection = null;
        }
    }
}
