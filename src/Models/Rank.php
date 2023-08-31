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
            Sex as gender FROM {$_ENV['BASE_SERVER']}.dbo.Rank_Temporada ORDER BY Rank ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function selectTopOnline($BaseUser) {

        $conn = $this->db->get();

        $stmt = $conn->query("SELECT TOP 10 
            NickName as nickname, 
            Grade as level, 
            Total as matches, 
            Win as wins, 
            OnlineTime as online_time 
            FROM $BaseUser.dbo.Sys_Users_Detail WHERE IsExist ='true' ORDER BY OnlineTime DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function selectTopPoder($BaseUser) {

        $conn = $this->db->get();

        $stmt = $conn->query("SELECT TOP 10 
            NickName as nickname, 
            Grade as level, 
            Total as matches, 
            Win as wins, 
            FightPower as power 
            FROM $BaseUser.dbo.Sys_Users_Detail WHERE IsExist ='true' ORDER BY FightPower DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function selectTopPvp($BaseUser) {

        $conn = $this->db->get();

        $stmt = $conn->query("SELECT TOP 10 
            NickName as nickname, 
            Grade as level, 
            Total as matches, 
            Win as wins, 
            FightPower as power 
            FROM $BaseUser.dbo.Sys_Users_Detail WHERE IsExist ='true' ORDER BY Win DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}