<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Email extends Model {

    public function insertEmailActivationToken(int $userId, $token, string $data) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.activate_email(userID, token, Date) VALUES(:userID, :token, :Date)");
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':Date', $data);
        $stmt->execute();        
    }

    public function selectEmailChangeRequest(int $userId) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.change_email WHERE UserId = :id ORDER BY Date DESC");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertEmailChangeRequest(int $userId, $token, $date) :bool {

        $conn = $this->db->get();

        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.change_email(userID, token, Date) VALUES(:id, :token, :date)");
        $stmt->bindParam(':id', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':date', $date);
        $stmt->execute();       
        $id = $conn->lastInsertId();
        $conn->commit();
        return ($id > 0) ? true : false;  
    }
}