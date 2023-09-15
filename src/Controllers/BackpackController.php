<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Backpack;
use App\Zaptank\Models\Server;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class BackpackController {

    public function listItems(Request $request, Response $response, array $args) :Response {
        
        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $account_email = $payload['email'];

        $backpack = new Backpack;

        if($backpack->selectBackpackItemCount($account_email) == 0) {
            $body = json_encode([
                'message' => 'Sua mochila está vazia!',
                'status_code' => 'empty_backpack'
            ]);
            $response->getBody()->write($body);
            return $response;
        } 

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;
        $baseTank = $server->baseTank;

        $itemsQuery = $backpack->selectBackpackItems($serverId, $baseTank, $account_email);

        $items = [];

        foreach($itemsQuery as $key => $item) {

            $Id = $cryptography->EncryptText($item['ID']);
            $templateId = $cryptography->EncryptText($item['TemplateID']);
            $count = $item['Count'];
            $categoryId = $item['CategoryID'];
            $needSex = $item['NeedSex'];
            $imagePath = '/teste';
            $backpackItemStatus = $item['Status' . $serverId];

            array_push($items, [
                'questii' => $Id, // id
                'category_id' => $categoryId,
                'count' => $count,
                'questi' => $templateId, // template_id
                'image_path' => 'teste',
                'need_sex' => $needSex, 
                'status' => $backpackItemStatus
            ]);
        }

        $body = json_encode([
            'data' => [
                'resource' => $_ENV['RESOURCE'],
                'items' => $items
            ]
        ]);
        $response->getBody()->write($body);
        return $response;
    }
}