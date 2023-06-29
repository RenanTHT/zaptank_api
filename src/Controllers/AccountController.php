<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;

class AccountController {

    /**
     * Cadastra nova conta
     */
    public function new(Request $request, Response $response) {

        $email             = $_POST['email'];
        $password          = strtoupper(md5($_POST['password']));
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Account;
        
        if(empty(trim($_POST['email'])) || empty(trim($_POST['password'])) || empty(trim($_POST['phone']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
        } else if(!empty($account->selectByEmail($email))) {
            $body = json_encode([
                'success' => false,
                'message' => 'e-mail em uso.',
                'status_code' => 'email_exists'
            ]);
        } else if(!empty($account->selectByPhone($phone))) {
            $body = json_encode([
                'success' => false,
                'message' => 'telefone em uso.',
                'status_code' => 'phone_exists'
            ]);
        } else {

            $account->create($email, $password, $phone, $ReferenceLocation);
            $user = $account->selectByEmail($email);
            
            if(is_array($user) && !empty($user)) {

                $uid = $user['UserId'];
                $phone = $user['Telefone'];

                $token = new Token;

                $jwt_hash = $token->generateAuthenticationToken($payload = [
                    'sub' => $uid,
                    'email' => $email,
                    'phone' => $phone
                ]);

                $body = json_encode([
                    'success' => true,
                    'message' => 'usuário foi cadastrado com êxito.',
                    'status_code' => 'user_registered',
                    'data' => [
                        'userId' => $uid,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $phone,
                        'jwt_hash' => $jwt_hash
                    ]
                ]);
            } else {
                $body = json_encode([
                    'success' => false,
                    'message' => 'houve um erro interno, o usuário não foi cadastrado.',
                    'status_code' => 'internal_error'
                ]);
            }
        }

        $response->getBody()->write($body);
        return $response->withHeader('Content-Type', 'application/json');
    }
}