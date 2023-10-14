<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class VirtualBag extends Model {
    
    public function insertItem($email) {

        $conn = $this->db->get();
        
        $conn->query("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.Bag_Goods (UserName, TemplateID, Count) VALUES (N'$email', N'2100000014', N'1')");
    }

    public function selectBackpackItemCount($email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as itemCount FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['itemCount'];
    }
    
    
    public function selectUnusedBackpackItemCount($serverId, $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as itemCount FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods WHERE UserName = :email and Status$serverId = '0'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['itemCount'];
    }
    
    public function selectBackpackItem($serverId, $Id, $templateId, $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods WHERE ID = :id and TemplateID = :template_id and UserName = :email and Status$serverId = '0'");
        $stmt->bindParam(':id', $Id);
        $stmt->bindParam(':template_id', $templateId);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function selectBackpackItems($serverId, $baseTank, $email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT A.ID, A.UserName, A.TemplateID, A.Count, A.Status{$serverId}, B.CategoryID, B.NeedSex, B.Pic FROM {$_ENV['BASE_SERVER']}.dbo.Bag_Goods A LEFT JOIN {$baseTank}.dbo.Shop_Goods B ON A.TemplateID = B.TemplateID WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($serverId, $Id, $templateId, $email) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Bag_Goods SET Status{$serverId} = 1 WHERE TemplateID = :template_id and ID = :id and UserName = :email");
        $stmt->bindParam(':id', $Id);
        $stmt->bindParam(':template_id', $templateId);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
}