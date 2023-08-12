<?php

namespace App\Zaptank\Controllers\Server;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Server;
use App\Zaptank\Helpers\Cryptography;

class ServerController {

    public function CheckServerSuvToken(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;

        if($server->search($decryptServer)) {
            $body = json_encode([
                'suv_token_is_valid' => true
            ]);
        } else {
            $body = json_encode([
                'suv_token_is_valid' => false,
                'message' => 'Não foi possível encontrar o servidor.'
            ]);
        }

        $response->getBody()->write($body);
        return $response;
    }
}