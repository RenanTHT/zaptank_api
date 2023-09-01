<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Invoice extends Model {
    
    public function store($full_name, $phone, $email, $vip_package, $method, $price, $status, $serverId) {

        $conn = $this->db->get();

        $stmt = $conn->query("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.Vip_Data (PacoteID, UserName, Method, Date, Price, Status, Name, Number, ServerID, IsChargeBack, PicPayLink) VALUES 
            ('$vip_package', '$email', '$method', getdate(), '$price', '$status', N'$full_name', '$phone', '$serverId', '0', '#')
        ");
    }

    public function selectLastInvoice($email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT TOP 1 ID FROM {$_ENV['BASE_SERVER']}.dbo.Vip_Data WHERE UserName = :email ORDER BY ID DESC");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (isset($result['ID'])) ? $result['ID'] : 0;
    }
}