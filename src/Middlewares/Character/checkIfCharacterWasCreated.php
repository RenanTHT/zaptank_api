<?php

namespace App\Zaptank\Middlewares\Character;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use App\Zaptank\Models\Character;
use App\Zaptank\Models\Server;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class checkIfCharacterWasCreated {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $suv = $route->getArgument('suv');
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $character = new Character;
        
        if($character->search($account_email, $baseUser) == true) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você já criou uma conta neste servidor.',
                'status_code' => 'character_creation_required'             
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;                
        }
        
        return $handler->handle($request);
    }
}