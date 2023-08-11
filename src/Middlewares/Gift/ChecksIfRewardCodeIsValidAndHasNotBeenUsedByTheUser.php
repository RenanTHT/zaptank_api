<?php

namespace App\Zaptank\Middlewares\Gift;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Gift;
use App\Zaptank\Services\Token;

class checksIfRewardCodeIsValidAndHasNotBeenUsedByTheUser {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        $giftCode = strtoupper($_POST['giftcode']);
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);

        $account_email = $payload['email'];        

        $gift = new Gift;

        if($gift->selectRewardCountByCode($giftCode) == 0) {

            $body = json_encode([
                'success' => false,
                'message' => 'Código de itens não encontrado!',
                'status_code' => 'reward_code_does_not_exist'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
            
        } else if($gift->SelectRewardCollectionRecordCountByUsernameAndCode($account_email, $giftCode) > 0) {

            $body = json_encode([
                'success' => false,
                'message' => 'Você já resgatou esse código uma vez.',
                'status_code' => 'the_code_has_already_been_redeemed'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        }
        
        return $handler->handle($request);
    }
}