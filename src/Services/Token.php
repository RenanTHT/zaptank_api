<?php

namespace App\Zaptank\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;

class Token {

    private $private_key;

    private $public_key;

    public function __construct() {
        $this->private_key = $_ENV['PRIVATE_KEY'];
        $this->public_key = $_ENV['PUBLIC_KEY'];
    }

    public function createJWT(array $payload) :string {

        $jwt = JWT::encode($payload, $this->private_key, 'HS256'); 

        return $jwt;
    }

    public function validate($jwt) {

        try {
            $decoded = JWT::decode($jwt, new Key($this->private_key, 'HS256'));
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
            $decoded = JWT::decode($jwt, new Key($this->private_key, 'HS256'));
            $decoded_array = (array) $decoded;
    
            return $decoded_array;        
        } catch (SignatureInvalidException $e) {
            return $e->getMessage();
        }
    }    
}