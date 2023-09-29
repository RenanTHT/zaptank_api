<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Services\Token;
use App\Zaptank\Models\Admin;

class AdminController {

    public function checkPermission(Request $request, Response $response, array $args) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $admin = new Admin;

        $administrator_has_permission = (!empty($admin->selectAdminByEmail($account_email)));

        $body = json_encode([
            'administrator_has_permission' => $administrator_has_permission
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}