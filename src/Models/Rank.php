<?php

namespace App\Zaptank\Models;

use App\Zaptank\Models\Model;
use \PDO;

class Rank extends Model {
    
    public function selectTopTemporada() {

        $conn = $this->db->get();

        $stmt = $conn->query("SELECT TOP 3
            Rank as rank, 
            Nome as nickname, 
            Level as level, 
            PartidasJogadas as matches, 
            PartidasGanhas as wins, 
            Poder as power,
            Style as style,
            Sex as gender FROM DB_Center.dbo.Rank_Temporada ORDER BY Rank ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}