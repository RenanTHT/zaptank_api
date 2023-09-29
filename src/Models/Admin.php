<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Admin extends Model {
    
    public function select() {

        $conn = $this->db->get();

        $query = $conn->query("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Admin_Permission");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectAdminByEmail($email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Admin_Permission WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}