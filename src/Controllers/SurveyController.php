<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Models\VirtualBag;
use App\Zaptank\Services\Token;

class SurveyController {

    public function store(Request $request, Response $response) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];   

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        if($user['VerifiedEmail'] == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por segurança, para utilizar a função você deve verificar seu e-mail.',
                'status_code' => 'unverified_email'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }
        
        if($user['Opinion'] == true) {
            $body = json_encode([
                'success' => false,
                'message' => 'Este usuário já participou da pesquisa.',
                'status_code' => 'survey_has_already_been_answered'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }

        if(!isset($_POST['method']) || empty($_POST['method'])) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não preencheu todos os campos solicitados.',
                'status_code' => 'empty_fields'
            ]);       
            
            $response->getBody()->write($body);
            return $response; 
        } else {
            $method = $_POST['method'];
        }

        $virtualBag = new VirtualBag;

        $account->updateReference($account_email, $method);
        $account->updateOpinion($account_email);
        $virtualBag->insertItem($account_email);

        $body = json_encode([
            'success' => true,
            'message' => 'A sua resposta foi enviada com sucesso!',
            'status_code' => 'reply_sent'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}