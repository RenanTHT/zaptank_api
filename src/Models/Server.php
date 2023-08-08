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

    public function search(string $suv) :void {

        $serverId = 1;

        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM Db_Center.dbo.Server_List WHERE ID = :serverId");
        $stmt->bindParam(':serverId', $serverId);
        $stmt->execute();
        $serverInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->setId($serverInfo['ID']);
        $this->setBaseUser($serverInfo['BaseUser']);
        $this->setBaseTank($serverInfo['BaseTank']);
        $this->setAreaId($serverInfo['AreaID']);
        $this->setQuestUrl($serverInfo['QuestUrl']);        
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