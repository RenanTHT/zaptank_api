<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Character extends Model {

    public $Id;
    public $userName;
    public $nickName;
    public $isExist;

    public function search($userName, $BaseUser) :bool {
        
        $conn = $this->db->get();
        
        $stmt = $conn->prepare("SELECT UserID, UserName, NickName, IsExist FROM {$BaseUser}.dbo.Sys_Users_Detail WHERE UserName = :userName");
        $stmt->bindParam(':userName', $userName);
        $stmt->execute();
        $characterInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!empty($characterInfo)) {
            $this->Id = $characterInfo['UserID'];
            $this->userName = $characterInfo['UserName'];
            $this->nickName = $characterInfo['NickName'];
            $this->isExist = $characterInfo['IsExist'];
            return true;
        } else {
            return false;
        }
    }


    public function store($userName, $nickname, $gender, $serverName, $areaId, $BaseUser) :void {

        $conn = $this->db->get();

        $conn->query("EXEC $BaseUser.dbo.SP_Users_Active @UserID='',@Attack=0,@Colors=N',,,,,,',@ConsortiaID=0,@Defence=0,@Gold=100000,@GP=1437053,@Grade=25,@Luck=0,@Money=0,@Style=N',,,,,,',@Agility=0,@State=0,@UserName=N'$userName',@PassWord=N'',@Sex='$gender',@Hide=1111111111,@Skin=N'',@Site=N''");
        
        if ($gender == 1) {
            $conn->query("EXEC $BaseUser.dbo.SP_Users_RegisterNotValidate @UserName=N'$userName',@PassWord=N'',@NickName=N'$nickname',@BArmID=7008,@BHairID=3158,@BFaceID=6103,@BClothID=5160,@BHatID=1142,@GArmID=7008,@GHairID=3158,@GFaceID=6103,@GClothID=5160,@GHatID=1142,@ArmColor=N'',@HairColor=N'',@FaceColor=N'',@ClothColor=N'',@HatColor=N'',@Sex='$gender',@StyleDate=0,@ServerName='$serverName',@AreaID='$areaId'");
        } else {
            $conn->query("EXEC $BaseUser.dbo.SP_Users_RegisterNotValidate @UserName=N'$userName',@PassWord=N'',@NickName=N'$nickname',@BArmID=7008,@BHairID=3244,@BFaceID=6204,@BClothID=5276,@BHatID=1214,@GArmID=7008,@GHairID=3244,@GFaceID=6202,@GClothID=5276,@GHatID=1214,@ArmColor=N'',@HairColor=N'',@FaceColor=N'',@ClothColor=N'',@HatColor=N'',@Sex='$gender',@StyleDate=0,@ServerName='$serverName',@AreaID='$areaId'");
        }
        $conn->query("EXEC $BaseUser.dbo.SP_Users_LoginWeb @UserName=N'$userName',@Password=N'',@FirstValidate=0,@NickName=N'$nickname'");
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