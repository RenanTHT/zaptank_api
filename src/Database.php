<?php

namespace App\Zaptank;

use \PDO;

class Database {

    private $conn;

    public function __construct() {
        $this->connect();
    }

    public function connect() :void {
        
        $pdoConfig = "{$_ENV['DB_DRIVER']}:Server={$_ENV['DB_HOST']};Database={$_ENV['BASE_SERVER']};";

        try {
            $pdo = new PDO($pdoConfig, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn = $pdo;
        } catch (PDOException $e) {
            $mensagem = "Drivers disponiveis: " . implode(",", PDO::getAvailableDrivers());
            $mensagem .= "\nErro: " . $e->getMessage();
            throw new Exception($mensagem);
        }        
    }

    public function close() :void {
        $this->conn = null;
    }

    public function get() :pdo {

        if($this->conn == null || !is_object($this->conn)) {
            $this->connect();
        }
        return $this->conn;
    }    
}