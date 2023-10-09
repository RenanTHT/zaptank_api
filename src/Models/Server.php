<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Server extends Model {
    
    public $Id;
    public $serverName;
    public $baseUser;
    public $baseTank;
    public $release;
    public $flashUrl;
    public $areaId;
    public $questUrl;
    public $maintenance;
    public $localhost;

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
            $this->setFlashUrl($serverInfo['FlashUrl']);
            $this->setAreaId($serverInfo['AreaID']);
            $this->setQuestUrl($serverInfo['QuestUrl']);
            $this->setMaintenance($serverInfo['Maintenance']);
            $this->setRelease($serverInfo['Release']);
            $this->setIsLocalhost($serverInfo['IsLocalHost']);
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

    public function setFlashUrl($flashUrl) {
        $this->flashUrl = $flashUrl;
    }

    private function setAreaId($areaId) {
        $this->areaId = $areaId;
    }

    private function setQuestUrl($questUrl) {
        $this->questUrl = $questUrl;
    }
    
    private function setMaintenance($maintenance) {
        $this->maintenance = $maintenance;
    }

    public function setRelease($release) {
        $this->release = $release;
    }

    public function setIsLocalhost($isLocalhost) {
        $this->localhost = $isLocalhost;
    }
}