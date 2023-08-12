<?php

namespace App\Zaptank\Middlewares\Server;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

use App\Zaptank\Models\Server;
use App\Zaptank\Helpers\Cryptography;

class checkIfServerSuvParameterIsInvalid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $suv = $route->getArgument('suv');

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;

        if($server->search($decryptServer) == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Não foi possível encontrar o servidor.',
                'status_code' => 'invalid_suv_token'             
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        }

        return $handler->handle($request);
    }
}