<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Backpack extends Model {
    
    public function selectBackpackItemCount($email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as itemCount FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['itemCount'];
    }
    
    public function selectBackpackItems($serverId, $baseTank, $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT A.ID, A.UserName, A.TemplateID, A.Count, A.Status{$serverId}, B.CategoryID, B.NeedSex, B.Pic FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods A LEFT JOIN {$baseTank}.dbo.Shop_Goods B ON A.TemplateID = B.TemplateID WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}