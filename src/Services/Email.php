<?php

namespace App\Zaptank\Services;

class Email {

    private $host;
    private $username;
    private $password;

    public function __construct() {
        $this->host = $_ENV['HOST'];
        $this->username = $_ENV['USERNAME'];
        $this->password = $_ENV['PASSWORD'];
    }
}