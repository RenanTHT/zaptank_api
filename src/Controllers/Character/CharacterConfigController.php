<?php

namespace App\Zaptank\Controllers\Character;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Server;
use App\Zaptank\Models\Account;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\CharacterMail;
use App\Zaptank\Models\Gift;

use App\Zaptank\Services\Token;
use App\Zaptank\Services\CurlRequest;

use App\Zaptank\Helpers\Cryptography;

class CharacterConfigController {

    public function changenick(Request $request, Response $response) :Response {
        
        $new_nick = $_POST['newnick'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $account_email = $payload['email'];
        $uid = $payload['sub'];

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        if($user['VerifiedEmail'] == false) {
            $body = json_encode([
                'success' => false,
                'message' => 'Por segurança, para alterar o nome do personagem você deve verificar seu e-mail.',
                'status_code' => 'unverified_email'
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


    public function clearbag(Request $request, Response $response) :Response {

        if(empty($_POST['password'])) {
            $body = json_encode([
                'success' => false,
                'message' => 'A confirmação da senha está vazio.',
                'status_code' => 'empty_password_confirmation'
            ]);     
            
            $response->getBody()->write($body);
            return $response;
        } else {
            $password = strtoupper(md5($_POST['password']));
            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
    
            $token = new Token;
            $payload = $token->decode($jwt);
    
            $uid = $payload['sub'];
            $account_email = $payload['email'];
    
            $account = new Account;
    
            if(empty($account->selectByUserAndPassword($account_email, $password))) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'A confirmação da senha está incorreta!',
                    'status_code' => 'incorrect_password',
                    'data' => $account->selectByUserAndPassword($account_email, $password)
                ]);    
                
                $response->getBody()->write($body);
                return $response;
            }

            $character = new Character;

            if($character->getCharacterStateByUsername($account_email) == 1) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Sua conta está online, saia do jogo para limpar a mochila.',
                    'status_code' => 'user_is_online'
                ]);    
                
                $response->getBody()->write($body);
                return $response;                   
            }     
            
            $character->updateCharacterBag($uid);

            $body = json_encode([
                'success' => true,
                'message' => 'Sua mochila foi limpa com sucesso!',
                'status_code' => 'clean_backpack'
            ]);

            $response->getBody()->write($body);
            return $response;
        }  
    }


    public function redeemGiftCode(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $giftCode = strtoupper($_POST['giftcode']);
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $gift = new Gift;
        $characterMail = new CharacterMail;
        $character = new Character;

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;
        
        $character->search($account_email, $baseUser);
        $characterId = $character->Id;
        $characterNickname = $character->nickName;        
        
        $rewardInfo = $gift->selectRewardInfoByCode($giftCode);
        $templateId = $rewardInfo['TemplateID'];
        $count = $rewardInfo['Count'];
        
        $characterMail->SP_Admin_SendUserItem($characterId, $characterNickname, $templateId, $count);
        $gift->StoreUserRewardCollectionRecord($account_email, $count, $giftCode);

        $curlRequest = new CurlRequest;
        $curlRequest->post("{$server->questUrl}/UpdateMailByUserID.ashx?UserID=$characterId&AreaID={$server->areaId}&key=TqUserZap777");

        $body = json_encode([
            'success' => true,
            'message' => 'Sucesso no resgate, sua recompensa foi enviada para seu correio!',
            'status_code' => 'reward_sent_successfully'
        ]);
        
        $response->getBody()->write($body);
        return $response;        
    }
}