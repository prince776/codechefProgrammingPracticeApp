<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

    class db
    {
        private $dbhost;
        private $dbname;
        private $dbuser;
        private $dbpass;

        // Connect to DB
        public function connect()
        {
            $this->dbhost = $_ENV['DBHOST'];
            $this->dbname = $_ENV['DBNAME'];
            $this->dbuser = $_ENV['DBUSER'];
            $this->dbpass = $_ENV['DBPASS'];

            $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname;";
            $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $dbConnection;
        }
    }