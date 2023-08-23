<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Ticket extends Model {
    
    public function create($email, $nickname, $characterId, $description, $subject, $phone, $serverId) :void {
        $conn = $this->db->get();
        $conn->query("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.Tickets (UserName, NickName, Email, UserID, Texto, Data, Status, CheckBox, Number, EvaluationStars, EvaluationText, IsEvaluation, SolvedBy, ServerID) VALUES (N'$email', N'$nickname', N'$email', N'$characterId', N'$description', getdate(), '0', N'$subject', N'$phone', N'0', N'Sem Avaliação', N'0', N'Não resolvido', N'$serverId')");
    }    


    public function getCountOfOpenTickets($email) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as openTicketCount FROM {$_ENV['BASE_SERVER']}.dbo.Tickets WHERE Status = '0' and UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['openTicketCount'];
    }
}