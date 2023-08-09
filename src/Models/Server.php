<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Server extends Model {
    
    public $id;
    public $baseUser;
    public $baseTank;
    public $areaId;
    public $questUrl;

    public function search($serverId) :bool {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM Db_Center.dbo.Server_List WHERE ID = :serverId");
        $stmt->bindParam(':serverId', $serverId);
        $stmt->execute();
        $serverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($serverInfo)) {        
            $this->setId($serverInfo['ID']);
            $this->setBaseUser($serverInfo['BaseUser']);
            $this->setBaseTank($serverInfo['BaseTank']);
            $this->setAreaId($serverInfo['AreaID']);
            $this->setQuestUrl($serverInfo['QuestUrl']);
            return true;
        } else {
            return false;
        }
    }

    private function setId($id) {
        $this->id = $id;
    }
    
    private function setBaseUser($baseUser) {
        $this->baseUser = $baseUser;
    }

    private function setBaseTank($baseTank) {
        $this->baseTank = $baseTank;
    }

    private function setAreaId($areaId) {
        $this->areaId = $areaId;
    }

    private function setQuestUrl($questUrl) {
        $this->questUrl = $questUrl;
    }
}