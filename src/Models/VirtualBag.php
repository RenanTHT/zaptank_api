<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class VirtualBag extends Model {
    
    public function insertItem($email) {

        $conn = $this->db->get();
        
        $conn->query("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.Bag_Goods (UserName, TemplateID, Count) VALUES (N'$email', N'2100000014', N'1')");
    }
}