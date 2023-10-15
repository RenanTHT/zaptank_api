<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Item extends Model {
    
    public function selectItemSexByTemplateId($templateId, $baseTank) {

        $conn = $this->db->get();

        $query = $conn->query("SELECT NeedSex FROM $baseTank.dbo.Shop_Goods WHERE TemplateID = $templateId");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result['NeedSex'];
    }
}