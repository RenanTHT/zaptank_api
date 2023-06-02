<?php

namespace App\Zaptank\Account;

use App\Zaptank\Database;

class Account {

    private $db;

    public function __construct() {
        $this->db = new Database;
    }
    
    public function register($email, $password, $phone, $ReferenceLocation) {
        $query = $this->db->get()->query("EXEC {$_ENV['BASE_SERVER']}.dbo.Webshop_Register @ApplicationName=N'DanDanTang',@password=N'{$password}',@email='{$email}',@passtwo = '{$password}',@error = 0, @VerifiedEmail = 0, @phone=N'{$phone}',@Reference=N'{$ReferenceLocation}'");
    }
}