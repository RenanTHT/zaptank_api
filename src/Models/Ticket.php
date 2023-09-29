<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Ticket extends Model {
    
    public function create($email, $nickname, $characterId, $description, $subject, $phone, $serverId) :void {
        $conn = $this->db->get();
        $conn->query("INSERT INTO {$_ENV['BASE_SERVER']}.dbo.Tickets (UserName, NickName, Email, UserID, Texto, Data, Status, CheckBox, Number, EvaluationStars, EvaluationText, IsEvaluation, SolvedBy, ServerID) VALUES (N'$email', N'$nickname', N'$email', N'$characterId', N'$description', getdate(), '0', N'$subject', N'$phone', N'0', N'Sem Avaliação', N'0', N'Não resolvido', N'$serverId')");
    }    

    public function selectUnresolvedTickets() {

        $conn = $this->db->get();
        $stmt = $conn->prepare("SELECT ID as ticket_id, UserName as user_name, NickName as nick_name, UserID as user_id, Texto as ticket_description, Data as ticket_created, Number as phone, CheckBox as subject, ServerID as server_id FROM {$_ENV['BASE_SERVER']}.dbo.Tickets WHERE Status = '0' ORDER BY Data");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectById($ticketId) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT * FROM {$_ENV['BASE_SERVER']}.dbo.Tickets WHERE ID = :ticket_id");
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCountOfOpenTickets($email) {
        
        $conn = $this->db->get();

        $stmt = $conn->prepare("SELECT count(*) as openTicketCount FROM {$_ENV['BASE_SERVER']}.dbo.Tickets WHERE Status = '0' and UserName = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['openTicketCount'];
    }

    public function updateStatus($ticketId, $status) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Tickets SET Status = :status WHERE ID = :ticket_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
    }
    
    public function updateSolvedBy($ticketId, $solvedBy) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Tickets SET SolvedBy = :solved_by WHERE ID = :ticket_id");
        $stmt->bindParam(':solved_by', $solvedBy);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
    }

    public function updateEvaluationStars($ticketId, $rating) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Tickets SET EvaluationStars = :rating WHERE ID = :ticket_id");
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
    }
    
    public function updateEvaluationText($ticketId, $text) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Tickets SET EvaluationText = :text WHERE ID = :ticket_id");
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
    }
    
    public function updateIsEvaluation($ticketId, $isEvaluation) :void {

        $conn = $this->db->get();

        $stmt = $conn->prepare("UPDATE {$_ENV['BASE_SERVER']}.dbo.Tickets SET IsEvaluation = :is_evaluation WHERE ID = :ticket_id");
        $stmt->bindParam(':is_evaluation', $isEvaluation);
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();
    }
}