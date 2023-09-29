<?php

namespace App\Zaptank\Middlewares\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Services\Token;
use App\Zaptank\Models\Admin;

class checkIfTheUserDoesNotHaveAdministratorPermissions {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $admin = new Admin;

        if(empty($admin->selectAdminByEmail($account_email))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você precisa ter a permissão de administrador para o uso desta função.',
                'status_code' => 'permission_required'
            ]);
            
            $response = new Response;
            $response->getBody()->write($body);
            return $response;
        }
        
        return $handler->handle($request);
    }
}