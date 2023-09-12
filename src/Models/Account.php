<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Account extends Model {

    public function create($email, $password, $phone, $ReferenceLocation) :void {

        $conn = $this->db->get();

        $query = $conn->query("EXEC {$_ENV['BASE_SERVER']}.dbo.Webshop_Register @ApplicationName=N'DanDanTang',@password=N'{$password}',@email='{$email}',@passtwo = '{$password}',@error = 0, @VerifiedEmail = 0, @phone=N'{$phone}',@Reference=N'{$ReferenceLocation}'");
    }


    public function selectByUserAndPassword(string $email, $password) :array {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT UserId, IsBanned, VerifiedEmail, Email, Telefone, Opinion FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Email = :email and Password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (!empty($result)) ? $result: [];
    }


    public function selectByEmail(string $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT UserId, IsBanned, VerifiedEmail, Email, Telefone, Opinion FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Email = :email");
		$stmt->bindParam(':email', $email);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function selectByPhone(string $phone) {

        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT UserId, IsBanned, VerifiedEmail, Email, Telefone, Opinion FROM {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo WHERE Telefone = :phone");
		$stmt->bindParam(':phone', $phone);
		$stmt->execute();		
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);       
    }


    public function updatePhone($userId, $phone) :bool {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET Telefone = :phone WHERE UserID= :id");
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return ($stmt->rowCount() > 0) ? true: false;
    }
    
    
    public function updatePassword($userId, $newPassword) :bool {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET Password = :password WHERE UserID= :id");
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return ($stmt->rowCount() > 0) ? true: false;
    }


    public function updateEmail($current_email, $new_email) {

        $conn = $this->db->get();

        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET Email = '$new_email' WHERE Email = '$current_email'");
        $query = $conn->query("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Server_List");
        $result = $query->fetchAll();
        foreach ($result as $infoBase) {
            $BaseUser = $infoBase['BaseUser'];
            $conn->query("UPDATE $BaseUser.dbo.Sys_Users_Detail SET UserName = '$new_email' WHERE UserName = '$current_email'");
        }
        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.Bag_Goods SET UserName = '$new_email' WHERE UserName='$current_email'");
        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET UserName = '$new_email' WHERE UserName='$current_email'");
        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.User_Award_GiftCode SET UserName = '$new_email' WHERE UserName='$current_email'");
        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET BadMail='0' WHERE Email='$new_email'");
        $conn->query("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET VerifiedEmail='0' WHERE Email='$new_email'");
    }


    public function updateReference($email, $reference) :void {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET Reference = :reference WHERE Email = :email");
        $stmt->bindParam(':reference', $reference);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
    
    
    public function updateOpinion($email) :void {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET Opinion = 1 WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
    
    
    public function updateIsFirstCharge($email) :void {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Mem_UserInfo SET IsFirstCharge = 'False' WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
}