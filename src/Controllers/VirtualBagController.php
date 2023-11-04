<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\VirtualBag;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\CharacterMail;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\CurlRequest;
use App\Zaptank\Helpers\Extra;

class VirtualBagController {

    public function listItems(Request $request, Response $response, array $args) :Response {
        
        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $account_email = $payload['email'];

        $virtualBag = new VirtualBag;

        if($virtualBag->selectBackpackItemCount($account_email) == 0) {
            $body = json_encode([
                'message' => 'Sua mochila está vazia!',
                'status_code' => 'empty_backpack',
                'data' => [
                    'resource' => $_ENV['RESOURCE'],
                    'items' => []
                ]
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

        $itemsQuery = $virtualBag->selectBackpackItems($serverId, $baseTank, $account_email);

        $items = [];

        foreach($itemsQuery as $key => $item) {

            $Id = $cryptography->EncryptText($item['ID']);
            $templateId = $cryptography->EncryptText($item['TemplateID']);
            $count = $item['Count'];
            $categoryId = $item['CategoryID'];
            $needSex = $item['NeedSex'];
            $imagePath = Extra::loadImage($needSex, $categoryId, $item['Pic']);
            $backpackItemStatus = $item['Status' . $serverId];

            array_push($items, [
                'questii' => $Id, // id
                'category_id' => $categoryId,
                'count' => $count,
                'questi' => $templateId, // template_id
                'image_path' => $imagePath,
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

    public function sendItem(Request $request, Response $response, array $args) :Response {

        if((!isset($_POST['questi']) || empty(trim($_POST['questi']))) || (!isset($_POST['questii']) || empty(trim($_POST['questii'])))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Parâmetros inválidos.',
                'status_code' => 'empty_params'
            ]);
            $response->getBody()->write($body);
            return $response;     
        }

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;
        $baseUser = $server->baseUser;
        $areaId = $server->areaId;
        $questUrl = $server->questUrl;

        $Id = $cryptography->DecryptText($_POST['questii']);
        $templateId = $cryptography->DecryptText($_POST['questi']);

        $virtualBag = new VirtualBag;
        $item = $virtualBag->selectBackpackItem($serverId, $Id, $templateId, $account_email);

        if(empty($item)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Não conseguimos processar sua solicitação...',
                'status_code' => 'invalid_params'
            ]);
            $response->getBody()->write($body);
            return $response;
        } else {
            $count = $item['Count'];
        }

        if($templateId == "-200") {
            $coupons = $count;
            $count = "0";
        } else {
            $coupons = "0";
        }

        $character = new Character;
        $character->search($account_email, $baseUser);
        $characterId = $character->Id;
        $characterNickname = $character->nickName; 

        $characterMail = new CharacterMail;
        $characterMail->SP_Admin_SendUserItem($characterId, $characterNickname, $templateId, $count, $baseUser);
        $virtualBag->updateStatus($serverId, $Id, $templateId, $account_email);

        CurlRequest::post("{$questUrl}/UpdateMailByUserID.ashx?UserID={$characterId}&AreaID={$areaId}&key=LizardGamesTqUserZap500K777");

        $body = json_encode([
            'success' => true,
            'message' => 'O item foi enviado para seu correio!',
            'status_code' => 'virtual_backpack_item_sent_successfully'
        ]);
        $response->getBody()->write($body);
        return $response;
    }
}