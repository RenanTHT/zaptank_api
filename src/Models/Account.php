<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;

class Account extends Model {

    public function store ($email, $password, $phone, $ReferenceLocation) {
        $query = $this->db->get()->query("EXEC {$_ENV['BASE_SERVER']}.dbo.Webshop_Register @ApplicationName=N'DanDanTang',@password=N'{$password}',@email='{$email}',@passtwo = '{$password}',@error = 0, @VerifiedEmail = 0, @phone=N'{$phone}',@Reference=N'{$ReferenceLocation}'");
        return 'store';
    }
}