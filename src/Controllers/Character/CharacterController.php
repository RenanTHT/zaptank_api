<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Character;
use App\Zaptank\Models\Server;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class CharacterController {

    public function new(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $nickname = $_POST['nickname'];
        $gender = $_POST['gender'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);

        $character = new Character;
        $character->store($account_email, $nickname, $gender, $server->serverName, $server->areaId, $server->baseUser);

        $body = json_encode([
            'success' => true,
            'message' => 'Personagem criado com sucesso!',
            'status_code' => 'character_created'
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    
    public function checkIfCharacterWasCreated(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

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
        $character_is_created = $character->search($account_email, $baseUser);

        $body = json_encode([
            'character_is_created' => $character_is_created
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}