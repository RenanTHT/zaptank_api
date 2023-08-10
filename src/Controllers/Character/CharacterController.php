<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Character;
use App\Zaptank\Services\Token;

class CharacterController {

    public function checkIfCharacterWasCreated(Request $request, Response $response) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);
        $account_email = $payload['email'];

        $character = new Character;
        $character_is_created = $character->search($account_email);

        $body = json_encode([
            'character_is_created' => $character_is_created
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}