<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Account extends Model {

    public function create ($email, $password, $phone, $ReferenceLocation) {

        $conn = $this->db->get();

        $query = $conn->query("EXEC {$_ENV['BASE_SERVER']}.dbo.Webshop_Register @ApplicationName=N'DanDanTang',@password=N'{$password}',@email='{$email}',@passtwo = '{$password}',@error = 0, @VerifiedEmail = 0, @phone=N'{$phone}',@Reference=N'{$ReferenceLocation}'");
        return 'store';
    }


    /**
     * Seleciona dados pelo e-mail
     */
    public function selectByEmail(string $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Email = :email");
		$stmt->bindParam(':email', $email);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Seleciona dados pelo telefone
     */
    public function selectByPhone(string $phone) {

        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Telefone = :phone");
		$stmt->bindParam(':phone', $phone);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);       
    }
}