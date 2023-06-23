<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;

class AuthController {

    public function make(Request $request, Response $response) {

        $email = $_POST['email'];
        $password =  strtoupper(md5($_POST['password']));

        $account = new Account;

        if($account->selectByEmail($email) == false) {
            
            $body = [
                'success' => false,
                'message' => 'E-mail não encontrado'
            ];
        } else {

            $user = $account->selectByUserAndPassword($email, $password);

            if(is_array($user) && !empty($user)) {

                if($user['IsBanned'] == true) {

                    $body = [
                        'success' => false,
                        'message' => 'Usuário banido' 
                    ];
                } else {

                    $uid = $user['UserId'];
                    $phone = $user['Telefone'];

                    // passo 4 - gerar token
                    $token = new Token;

                    $jwt_hash = $token->generateAuthenticationToken($uid, $email, $phone);

                    $body = [
                        'success' => true,
                        'message' => 'Autenticação bem-sucedida',
                        'data' => [
                            'userId' => $uid,
                            'email' => $email,
                            'password' => $password,
                            'phone' => $phone,
                            'jwt_hash' => $jwt_hash
                        ]
                    ];                
                }

            } else {

                $body = [
                    'success' => false,
                    'message' => "Senha incorreta"
                ];
            }    
        }

        $response->getBody()->write(json_encode($body));
        
        return $response;
    }
}