<?php

namespace App\Zaptank\Middlewares\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Account;
use App\Zaptank\Services\Token;

class ChecksIfAccountEmailIsNotVerified {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

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

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        }

        return $handler->handle($request);
    }
}