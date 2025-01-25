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
        // define('HOSTNAME', "fdb1030.atspace.me");
        // define('USERNAME', "4579443_jobapplication");
        // define('PASSWORD', "![rp[kIB5AyH^*j_");
        // define('DATABASE', "4579443_jobapplication");

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
        return $this->dbConnection ? $this->dbConnection : null;
    }


    public function close()
    {
        if ($this->dbConnection) {
            mysqli_close($this->dbConnection);
            $this->dbConnection = null;
        }
    }
}
