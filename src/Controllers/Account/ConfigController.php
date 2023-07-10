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
                    'status_code' => 'changed'                
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
        $response->getBody()->write('change password');
        return $response;
    }
}