<?php

namespace App\Zaptank\Controllers\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;

class ConfigController {
    
    public function changePhone(Request $request, Response $response) : Response {

        $phone = $_POST['phone'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);

        $uid = $payload['sub'];

        if(empty($phone)) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
        } else if(strlen($phone) < 19) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por favor, preencha o número de telefone corretamente.',
                'status_code' => 'invalid_number'                
            ]);
        } else {
            $account = new Account;

            if(!empty($account->selectByPhone($phone))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'O telefone está sendo usado.',
                    'status_code' => 'phone_exists'                
                ]);                
            } else {
                $account->updatePhone($uid, $phone);                

                $body = json_encode([
                    'success' => true,
                    'message' => 'Telefone alterado com sucesso!',
                    'status_code' => 'phone_changed'                
                ]);                 
            }
        }

        /*
        $file = fopen('log.txt', 'a');
        fwrite($file, date('d/m/Y H:i:s') . "\n");
        fclose($file);
        */
        
        $response->getBody()->write($body);
        return $response;
    }


    public function changePassword(Request $request, Response $response) : Response {

        $oldpass = md5($_POST['oldpass']);
        $newpass = md5($_POST['newpass']);

        if(empty($oldpass)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a senha antiga.',
                'status_code' => 'empty_oldpass'
            ]);
        } else if(empty($newpass)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não informou a nova senha.',
                'status_code' => 'empty_oldpass'
            ]);            
        } else {
            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $token = new Token;
            $payload = $token->validate($jwt);
    
            $uid = $payload['sub'];
            $email = $payload['email'];

            $account = new Account;
            
            if(empty($account->selectByUserAndPassword($email, $oldpass))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Senha antiga incorreta, confira os dados digitados...',
                    'status_code' => 'incorrect_old_password'
                ]);                
            } else {
                $user = $account->selectByEmail($email);
                
                if($user['VerifiedEmail'] == false) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Por segurança, para alterar sua senha você deve verificar seu e-mail.',
                        'status_code' => 'unverified_email'
                    ]);
                } else if(!empty($account->selectByUserAndPassword($email, $newpass))) {   
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Nova senha digitada é a mesma que a antiga...',
                        'status_code' => 'unverified_email'
                    ]);                
                } else {
                    // - Atualiza nova senha banco
                    $account->updatePassword($uid, $newpass);

                    // - Envia e-mail notificação

                    $body = json_encode([
                        'success' => true,
                        'message' => 'Senha alterada com sucesso, realize o login novamente.',
                        'status_code' => 'password_changed'
                    ]);               
                }
            }
        }
        
        $response->getBody()->write($body);
        return $response;
    }
}