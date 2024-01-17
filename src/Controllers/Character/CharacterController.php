<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Character;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Item;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class CharacterController {

    public function new(Request $request, Response $response, array $args) :Response {

        if (
            (!isset($_POST['nickname']) || empty(trim($_POST['nickname']))) ||
            (!isset($_POST['gender']) || trim($_POST['gender']) == '')
        ) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        $nickname = $_POST['nickname'];
        $gender = $_POST['gender'];

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        // Caso parâmetro suv for inválido
        if($decryptServer == false) {
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $serverName = $server->serverName;
        $baseUser = $server->baseUser;
        $areaId = $server->areaId;

        $character = new Character;

        if($character->getCharacterCountByNickname($nickname, $baseUser) > 0) {
            $body = json_encode([
                'success' => false,
                'message' => 'Já existe um usuário com este nick, por favor escolha outro.',
                'status_code' => 'nickname_already_exists'
            ]);
    
            $response->getBody()->write($body);
            return $response;
        }

        $character->store($account_email, $nickname, $gender, $serverName, $areaId, $baseUser);

        $body = json_encode([
            'success' => true,
            'message' => 'Personagem criado com sucesso!',
            'status_code' => 'character_created'
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function getCharacterDetails(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        // Caso parâmetro suv for inválido
        if($decryptServer == false) {
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $character = new Character;
        $character->search($account_email, $baseUser);
        
        $body = json_encode([
            'character' => [
                'nickname' => $character->nickName
            ]
        ]);

        $response->getBody()->write($body);
        return $response;
    }
    
    public function checkIfCharacterWasCreated(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);
        
        // Caso parâmetro suv for inválido
        if($decryptServer == false) {
            return $response->withStatus(500);  
        }

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


    public function getStyle(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;
        $baseTank = $server->baseTank;

        $character = new Character;
        $character->search($account_email, $baseUser);
        $gender = ($character->gender) ? 'm' : 'f';
        $style = $character->style;
        $level = $character->level;

        $styles = explode(',', $style);

        $head = explode('|', $styles[0]);
        $effect = explode('|', $styles[3]);
        $hair = explode('|', $styles[2]);
        $face = explode('|', $styles[5]);
        $cloth = explode('|', $styles[4]);    
        $arm = explode('|', $styles[6]);  

        $item = new Item;

        if(empty($head[1])) {
            $head = [
                'pic' => 'default',
                'sex' => $gender
            ];
        } else {
            $head = [
                'pic' => $head[1],
                'sex' => ($item->selectItemSexByTemplateId($head[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }

        if(empty($effect[1])) {
            $effect = [
                'pic' => 'default',
                'sex' => $gender
            ];
        } else {
            $effect = [
                'pic' => $effect[1],
                'sex' => ($item->selectItemSexByTemplateId($effect[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }

        if(empty($hair[1])) {
            $hair = [
                'pic' => 'default',
                'sex' => $gender
            ];
        } else {
            $hair = [
                'pic' => $hair[1],
                'sex' => ($item->selectItemSexByTemplateId($hair[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }

        if(empty($face[1])) {
            $face = [
                'pic' => 'default',
                'sex' => $gender
            ];
        } else {
            $face = [
                'pic' => $face[1],
                'sex' => ($item->selectItemSexByTemplateId($face[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }
        
        if(empty($cloth[1])) {
            $cloth = [
                'pic' => 'default',
                'sex' => $gender
            ];
        } else {
            $cloth = [
                'pic' => $cloth[1],
                'sex' => ($item->selectItemSexByTemplateId($cloth[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }
        
        if(empty($arm[1])) {
            $arm = [
                'pic' => 'axe',
                'sex' => $gender
            ];
        } else {
            $arm = [
                'pic' => $arm[1],
                'sex' => ($item->selectItemSexByTemplateId($arm[0], $baseTank) == 1) ? 'm' : 'f'
            ];
        }
        
        $data = [
            'character' => [
                'gender' => $gender,
                'style' => [
                    'head' => $head,
                    'effect' => $effect,
                    'hair' => $hair,
                    'face' => $face,
                    'cloth' => $cloth,
                    'arm' => $arm
                ],
                'level' => $level
            ]
        ];

        $body = json_encode([
            'data' => $data
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}