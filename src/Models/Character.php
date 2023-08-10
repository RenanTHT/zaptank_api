<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Character extends Model{

    public $Id;
    public $userName;
    public $nickName;

    public function search($userName, $BaseUser = 'Db_Tank_102') :bool {
        
        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT UserID, UserName, NickName FROM {$BaseUser}.dbo.Sys_Users_Detail WHERE UserName = :userName");
        $stmt->bindParam(':userName', $userName);
        $stmt->execute();
        $characterInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!empty($characterInfo)) {
            $this->Id = $characterInfo['UserID'];
            $this->userName = $characterInfo['UserName'];
            $this->nickName = $characterInfo['NickName'];
            return true;
        } else {
            return false;
        }
    }

    
    public function getCharacterCountByNickname($nickName, $BaseUser = 'Db_Tank_102') {
        
        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT count(*) as characterCount FROM {$BaseUser}.dbo.Sys_Users_Detail WHERE NickName = :nickname");
        $stmt->bindParam(':nickname', $nickName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['characterCount'];
    }
    
    
    public function getCharacterStateByUsername($email, $BaseUser = 'Db_Tank_102') {
        
        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT State FROM {$BaseUser}.dbo.Sys_Users_Detail WHERE UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['State'];
    }


    public function updateCharacterName($userId, $userName, $nickname, $BaseUser = 'Db_Tank_102') :void {

        $conn = $this->db->get();

        $conn->query("UPDATE {$BaseUser}.dbo.Sys_Users_Detail SET NickName=N'$nickname' WHERE UserName='$userName'");
        $conn->query("UPDATE {$BaseUser}.dbo.Consortia SET ChairmanName=N'$nickname' WHERE ChairmanID='$userId'");
        $conn->query("UPDATE {$BaseUser}.dbo.Consortia_Users SET UserName=N'$nickname' WHERE UserID='$userId'");        
    }


    public function updateCharacterBag($userId, $BaseUser = 'Db_Tank_102') :void {
        
        $conn = $this->db->get();

        $conn->query("UPDATE {$BaseUser}.dbo.Sys_Users_Goods SET IsExist=0 WHERE UserID='$userId' AND BagType=0 AND place >=80 AND StrengthenLevel = 0");
    }
}