<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Gift extends Model {

    public function SelectRewardInfoByCode($code, $serverId = 1) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Award_GiftCode WHERE Code = :code and ServerID = :id and IsActive = 1");
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':id', $serverId);
        $stmt->execute();
        return $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function selectRewardCountByCode($code, $serverId = 1) {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) AS rewardCount FROM {$_ENV['BASE_SERVER']}.dbo.Award_GiftCode WHERE Code = :code and ServerID = :id and IsActive = 1");
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':id', $serverId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rewardCount'];
    }    


    public function SelectRewardCollectionRecordCountByUsernameAndCode($userName, $code, $serverId = 1) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) AS rewardCollectionCount FROM {$_ENV['BASE_SERVER']}.dbo.User_Award_GiftCode WHERE UserName = :userName and ServerID = :id and Code = :code");
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':id', $serverId);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['rewardCollectionCount'];        
    }


    public function StoreUserRewardCollectionRecord($userName, $count, $code, $serverId = 1) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.User_Award_GiftCode (UserName, Count, ServerID, Code) VALUES (:userName, :count, :serverId, :code)");
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':serverId', $serverId);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
    }
}