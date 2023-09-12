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

    public function selectVipItemCountByPackageIdAndTemplateId($vipPackageId, $templateId) {

        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Vip_List_Item WHERE VipID = :vip_package_id and TemplateID = :template_id");
        $stmt->bindParam(':vip_package_id', $vipPackageId);
        $stmt->bindParam(':template_id', $templateId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (isset($result['Count'])) ? $result['Count'] : 0;
    }
}