<?php

namespace App\Zaptank\Middlewares\Character;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use App\Zaptank\Models\Character;
use App\Zaptank\Services\Token;

class checkIfCharacterWasNotCreated {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);
        $account_email = $payload['email'];

        $character = new Character;
        
        if($character->search($account_email) == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você deve criar o personagem antes de utilizar a função.',
                'status_code' => 'character_creation_required'             
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;                
        }
        
        return $handler->handle($request);
    }
}