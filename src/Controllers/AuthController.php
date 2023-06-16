<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;

class AuthController {

    public function make(Request $request, Response $response) {

        $email = $_POST['email'];
        $password = $_POST['password'];

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

                    // passo 4 - gerar token

                    $body = [
                        'success' => true,
                        'message' => 'Autenticação bem-sucedida',
                        'data' => [
                            'userId' => $user['UserId'],
                            'telefone' => $user['Telefone'],
                            'token' => md5(time())
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