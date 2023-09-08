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

    public function selectByIdAndUser($invoiceId, $email) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM Db_Center.dbo.Vip_Data WHERE ID = :invoice_id and UserName = :email");
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function selectInvoiceCountByIdAndUser($invoiceId, $email) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as invoiceCount FROM Db_Center.dbo.Vip_Data WHERE ID = :invoice_id and UserName = :email");
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['invoiceCount'];
    }

    public function selectLastInvoice($email) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT TOP 1 ID FROM {$_ENV['BASE_SERVER']}.dbo.Vip_Data WHERE UserName = :email ORDER BY ID DESC");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (isset($result['ID'])) ? $result['ID'] : 0;
    }

    public function updatePicpayQrCode($invoiceId, $picpayQrCode) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET PicPayQrCode = :picpay_qrcode WHERE ID = :invoice_id");
        $stmt->bindParam(':picpay_qrcode', $picpayQrCode);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
   
    public function updatePicpayLink($invoiceId, $picpayLink) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET PicPayLink = :picpay_link WHERE ID = :invoice_id");
        $stmt->bindParam(':picpay_link', $picpayLink);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
   
    public function updateReferenceKey($invoiceId, $referenceKey) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET KeyRef = :reference_key WHERE ID = :invoice_id");
        $stmt->bindParam(':reference_key', $referenceKey);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
    
    public function updatePixDataImage($invoiceId, $qrcodeImageUrl) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET PixDataImage = :qrcode_image_url WHERE ID = :invoice_id");
        $stmt->bindParam(':qrcode_image_url', $qrcodeImageUrl);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
    
    public function updateOrderNumber($invoiceId, $orderNumber) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET OrderNum = :order_number WHERE ID = :invoice_id");
        $stmt->bindParam(':order_number', $orderNumber);
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
    
    public function updateMethod($invoiceId) :void {

        $conn = $this->db->get();
        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Vip_Data SET Method = 'PIX' WHERE ID = :invoice_id");
        $stmt->bindParam(':invoice_id', $invoiceId);
        $stmt->execute();
    }
}