<?php

class DB_CONNECTION
{
    var $dbConnection = null;
    function connect()
    {
        define('HOSTNAME', "localhost");
        define('USERNAME', "root");
        define('PASSWORD', "");
        define('DATABASE', "job_application");

        // Connecting to mysql database
        $con = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE);
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        };
        $this->dbConnection = $con;
        // returning connection cursor
        return $this->dbConnection;
    }
    /* Function to close db connection */
    function close($connection)
    {
        // closing db connection
        mysqli_close($connection);
    }
}
