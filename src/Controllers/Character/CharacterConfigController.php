<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Models\Character;
use App\Zaptank\Services\Token;

class CharacterConfigController {

    public function changenick(Request $request, Response $response) :Response {
        
        $new_nick = $_POST['newnick'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->validate($jwt);

        $account_email = $payload['email'];
        $uid = $payload['sub'];

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        if($user['VerifiedEmail'] == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por segurança, para alterar o nome do personagem você deve verificar seu e-mail.',
                'status_code' => 'email_already_verified'
            ]); 

            $response->getBody()->write($body);
            return $response;
        }

        $character = new Character;
        
        if($character->getCharacterCountByNickname($new_nick) > 0) {
            $body = json_encode([
                'success' => false,
                'message' => 'Já existe um usuário com este nick, por favor escolha outro.',
                'status_code' => 'nickname_in_use'
            ]);    
            
            $response->getBody()->write($body);
            return $response;            
        }

        if($character->getCharacterStateByUsername($account_email) == 1) {
            $body = json_encode([
                'success' => false,
                'message' => 'Sua conta está online, saia do jogo para alterar seu nome.',
                'status_code' => 'user_is_online'
            ]);    
            
            $response->getBody()->write($body);
            return $response;                   
        }
        
        $character->updateCharacterName($uid, $account_email, $new_nick);

        $body = json_encode([
            'success' => true,
            'message' => 'O nome do personagem foi alterado com sucesso',
            'status_code' => 'nickname_changed'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}