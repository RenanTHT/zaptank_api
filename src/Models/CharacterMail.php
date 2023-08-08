<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class CharacterMail extends Model {
    
    public function SP_Admin_SendUserItem($characterId, $nickName, $templateId, $count, $BaseUser = 'Db_Tank_102') {

        $conn = $this->db->get();

        $conn->query("EXECUTE $BaseUser.dbo.SP_Admin_SendUserItem
            @ItemID = '$templateId'
            ,@UserID = '$characterId'
            ,@TemplateID = '$templateId'
            ,@Place = '0'
            ,@Count = '$count'
            ,@IsJudge = '0'
            ,@Color = ''
            ,@IsExist = '1'
            ,@StrengthenLevel = '0'
            ,@AttackCompose = '0'
            ,@DefendCompose = '0'
            ,@LuckCompose = '0'
            ,@AgilityCompose = '0'
            ,@IsBinds = '1'
            ,@ValidDate = '0'
            ,@BagType = '0'
            ,@ID = '0'
            ,@SenderID = '0'
            ,@Sender = 'Sistema'
            ,@ReceiverID = '$characterId'
            ,@Receiver = '$nickName'
            ,@Title = 'Recompensas de Código!'
            ,@Content = 'Esse item foi enviado por você através do nosso sistema de código.'
            ,@IsRead = '0'
            ,@IsDelR = '0'
            ,@IfDelS = '0'
            ,@IsDelete = '0'
            ,@Annex1 = '0'
            ,@Annex2 = '0'
            ,@Gold = '0'
            ,@Money = '0'
       ");        
    }
}