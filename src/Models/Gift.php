<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Gift extends Model {

    public function selectRewardCountByCode($code, $serverId = 1) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) AS rewardCount FROM Db_Center.dbo.Award_GiftCode WHERE Code = :code and ServerID = :id and IsActive = 1");
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':id', $serverId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rewardCount'];
    }    

    public function SelectRewardCollectionRecordCountByUsernameAndCode($userName, $code, $serverId = 1) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) AS rewardCollectionCount FROM Db_Center.dbo.User_Award_GiftCode WHERE UserName = :userName and ServerID = :id and Code = :code");
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':id', $serverId);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rewardCollectionCount'];        
    }
}