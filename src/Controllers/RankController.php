<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Rank;
use App\Zaptank\Models\Server;
use App\Zaptank\Helpers\Cryptography;

class RankController {

    public function listRankTemporada(Request $request, Response $response) :Response {

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