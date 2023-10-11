<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Zaptank\Models\Server;
use App\Zaptank\Models\Vip;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\Extra;

class VipController {

    public function list(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $vip = new Vip;
        $vipList = $vip->selectByServerId($decryptServer);
        
        $body = json_encode([
            'data' => [
                'vip_list' => $vipList
            ]
        ]);

        $response->getBody()->write($body);
        return $response;
    }
    
    public function details(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $vipPackageId = $args['id'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $baseTank = $server->baseTank;

        $vip = new Vip;
        $items = $vip->selectVipItemsInfo($baseTank, $vipPackageId);

        $size = sizeof($items);
        
        for($i=0; $i < $size; $i++) {

            $count = $items[$i]['Count'];
            $categoryId = $items[$i]['CategoryID'];
            $needSex = $items[$i]['NeedSex'];
            $pic = $items[$i]['Pic'];

            $path = Extra::loadImage($needSex, $categoryId, $pic);
            $link = $_ENV['RESOURCE'] . '/' . $path;

            $items[$i] = [
                'image' => $link,
                'count' => $count    
            ];
        }

        $body = json_encode([
            'data' => [
                'items' => $items
            ]
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}