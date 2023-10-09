<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Time;

class AuthController {

    public function make(Request $request, Response $response) :Response {

        if((!isset($_POST['email']) || empty(trim($_POST['email']))) || (!isset($_POST['password']) || empty(trim($_POST['password'])))) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        $email = $_POST['email'];
        $password = strtoupper(md5($_POST['password']));

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

                    $token = new Token;

                    $jwt_hash = $token->createJWT($payload = [
                        'sub' => $uid,
                        'email' => $email,
                        'exp' => Time::getTimestamp() + $_ENV['LOGIN_EXPIRATION_TIME_IN_SECONDS']
                    ]);

                    $body = [
                        'success' => true,
                        'message' => 'Autenticação bem-sucedida',
                        'data' => [
                            'userId' => $uid,
                            'email' => $email,
                            'password' => $password,
                            'phone' => $phone,
                            'verifiedEmail' => $user['VerifiedEmail'],
                            'opinion' => $user['Opinion'],
                            'badMail' => $user['BadMail'],
                            'isFirstCharge' => $user['IsFirstCharge'],
                            'jwt_authentication_hash' => $jwt_hash
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