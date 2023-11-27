<?php

namespace App\Zaptank\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;

class Token {

    private $secret_key;

    public function __construct() {
        $this->secret_key = $_ENV['SECRET_KEY'];
    }

    public function createJWT(array $payload) :string {

        $jwt = JWT::encode($payload, $this->secret_key, 'HS256'); 

        return $jwt;
    }

    public function validate($jwt) {

        try {
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            $decoded_array = (array) $decoded;
    
            return $decoded_array;        
        } catch (ExpiredException $e) {
            return 'Erro: o token de autenticação expirou.';
        } catch (SignatureInvalidException $e) {
            return 'Erro: a assinatura do token de autenticação é inválida.';
        }
    }    
    
    public function decode($jwt) {

        try {
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            $decoded_array = (array) $decoded;
    
            return $decoded_array;        
        } catch (SignatureInvalidException $e) {
            return $e->getMessage();
        }
    }    
}