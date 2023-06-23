<?php

namespace App\Zaptank\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Token {

    public function generateAuthenticationToken(int $sub, string $email, string $phone) :string {

        $privateKey = $_ENV['PRIVATE_KEY'];

        $payload = [
            'sub' => $sub,
            'email' => $email,
            'phone' => $phone
        ];

        $jwt = JWT::encode($payload, $privateKey, 'HS256'); 

        return $jwt;
    }
}