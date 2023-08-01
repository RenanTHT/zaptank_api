<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CharacterConfigController {

    public function changenick(Request $request, Response $response) :Response {
        $body = json_encode([
            'success' => true,
            'message' => 'nick alterado com sucesso'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}