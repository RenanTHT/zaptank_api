<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;

class AccountController {

    /**
     * Cadastra nova conta
     */
    public function new(Request $request, Response $response) {

        $email             = $_POST['email'];
        $password          = $_POST['password'];
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Account;

        if(!empty($account->selectByEmail($email))) {
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
                $body = json_encode([
                    'success' => true,
                    'message' => 'usuário foi cadastrado com êxito.',
                    'status_code' => 'user_registered',
                    'data' => $user
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