<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Vip extends Model {
    
    public function selectById($Id) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Vip_List WHERE ID = :id");
        $stmt->bindParam(':id', $Id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}