<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Account extends Model {

    public function create($email, $password, $phone, $ReferenceLocation) :void {

        $conn = $this->db->get();

        $query = $conn->query("EXEC {$_ENV['BASE_SERVER']}.dbo.Webshop_Register @ApplicationName=N'DanDanTang',@password=N'{$password}',@email='{$email}',@passtwo = '{$password}',@error = 0, @VerifiedEmail = 0, @phone=N'{$phone}',@Reference=N'{$ReferenceLocation}'");
    }


    /**
     * Criar registro na tabela de ativar email
     */
    public function insertEmailActivationToken(int $userId, $token, string $data) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.activate_email(userID, token, Date) VALUES(:userID, :token, :Date)");
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':Date', $data);
        $stmt->execute();        
    }


    public function selectByUserAndPassword(string $email, $password) :array {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT UserId, IsBanned, Telefone FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Email = :email and Password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (!empty($result)) ? $result: [];
    }


    public function selectByEmail(string $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT UserId, Email, Telefone FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Email = :email");
		$stmt->bindParam(':email', $email);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function selectByPhone(string $phone) {

        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT UserId, Email, Telefone FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Telefone = :phone");
		$stmt->bindParam(':phone', $phone);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);       
    }
}