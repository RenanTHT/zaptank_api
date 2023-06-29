<?php

namespace App\Zaptank\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token {

    private $private_key;

    public function __construct() {
        $this->private_key = $_ENV['PRIVATE_KEY'];
    }

    public function generateAuthenticationToken(array $payload) :string {

        $jwt = JWT::encode($payload, $this->private_key, 'HS256'); 

        return $jwt;
    }
}