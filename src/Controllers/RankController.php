<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Rank;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Item;
use App\Zaptank\Helpers\Cryptography;

class RankController {

    public function listRankTemporada(Request $request, Response $response, array $args) :Response {
        
        $suv = $args['suv'];
        
        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseTank = $server->baseTank;

        $rank = new Rank;

        $rankList = $rank->selectTopTemporada();

        foreach($rankList as $key => $rank) {
            
            $styles = explode(',', $rank['style']);
                                 
            $head = explode('|', $styles[0]);
            $effect = explode('|', $styles[3]);
            $hair = explode('|', $styles[2]);
            $face = explode('|', $styles[5]);
            $cloth = explode('|', $styles[4]);    
            $arm = explode('|', $styles[6]);    
            $gender = ($rank['gender']) ? 'm' : 'f';

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

            $rankList[$key]['style'] = [
                'head' => $head,
                'effect' => $effect,
                'hair' => $hair,
                'face' => $face,
                'cloth' => $cloth,
                'arm' => $arm
            ];           
        }

        $body = json_encode([
            'title' => 'Ranking de temporadada.',
            'data' => $rankList
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function listRankOnline(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $rank = new Rank;

        $rankList = $rank->selectTopOnline($baseUser);

        $body = json_encode([
            'title' => 'Ranking tempo online.',
            'data' => $rankList
        ]);

        $response->getBody()->write($body);
        return $response;
    } 

    public function listRankPoder(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $rank = new Rank;

        $rankList = $rank->selectTopPoder($baseUser);

        $body = json_encode([
            'title' => 'Ranking de poder',
            'data' => $rankList
        ]);

        $response->getBody()->write($body);
        return $response;
    } 
    
    public function listRankPvp(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $rank = new Rank;

        $rankList = $rank->selectTopPvp($baseUser);

        $body = json_encode([
            'title' => 'Ranking de pvp',
            'data' => $rankList
        ]);

        $response->getBody()->write($body);
        return $response;
    } 
}