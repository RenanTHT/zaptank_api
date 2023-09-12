<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class ChargeMoney extends Model {
    
    public function store($BaseUser, $UserName, $coupons, $date, $canUse, $method, $price) {

        $conn = $this->db->get();

        $stmt = $Connect->prepare("INSERT INTO {$BaseUser}.dbo.Charge_Money(UserName, Money, Date, CanUse, PayWay, NeedMoney) VALUES(:username, :money, :date, :can_use, :method, :price)");
        $stmt->bindParam(':username', $UserName);
        $stmt->bindParam(':money', $coupons);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':can_use', $canUse);
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':price', $price);
        $stmt->execute();
    }
}