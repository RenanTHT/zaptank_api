<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Server extends Model {
    
    public $Id;
    public $serverName;
    public $baseUser;
    public $baseTank;
    public $areaId;
    public $questUrl;

    public function search($serverId) :bool {

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Server_List WHERE ID = :serverId");
        $stmt->bindParam(':serverId', $serverId);
        $stmt->execute();
        $serverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!empty($serverInfo)) {        
            $this->setId($serverInfo['ID']);
            $this->setServerName($serverInfo['Name']);
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
        $this->Id = $id;
    }

    private function setServerName($serverName) {
        $this->serverName = $serverName;
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