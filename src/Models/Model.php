<?php

namespace App\Zaptank\Models;

use App\Zaptank\Database;

class Model {

    protected $db;

    public function __construct() {
        $this->db = new Database;
    }    
}