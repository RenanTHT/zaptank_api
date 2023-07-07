<?php

namespace App\Zaptank\Middlewares\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Zaptank\Services\Token;

class ensureJwtAuthTokenIsValid {

    public function __invoke(Request $request, RequestHandler $handler) : Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
        
        $token = new Token;
        $decode = $token->validate($jwt);
        
        if(!is_array($decode)) {

            $body = json_encode([
                'success' => false,
                'message' => 'Autênticação falhou.',
                'status_code' => 'unauthorized'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response->withStatus(401);        
        }
        
        return $handler->handle($request);
    }
}