<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Rank;

class RankController {

    public function listTemporada(Request $request, Response $response) :Response {

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
}