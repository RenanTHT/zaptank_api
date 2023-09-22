<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Password extends Model {
    
    public function selectFromPasswordResetTable($userId) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.reset_password WHERE userID = :userID and active = 1 ORDER BY data DESC");
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function selectFromPasswordResetTableWithToken($token) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.reset_password WHERE reset_token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function storeRecoverPasswordRequest($userId, $token, $date) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.reset_password(userID, reset_token, data) VALUES(:userID, :token, :date)");
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
    }
    
    public function updatePasswordRecoveryRequestDate($userId, $date) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.reset_password SET data = :date WHERE userID = :userID");
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
    }
    
    public function updatePasswordRecoveryRequestStatusWithToken($token) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.reset_password SET active = 0 WHERE reset_token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
    }
}