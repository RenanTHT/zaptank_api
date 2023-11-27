<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Admin;
use App\Zaptank\Models\Account;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\Server;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class PlayController {

    public function play(Request $request, Response $response, array $args) :Response {
          
        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $account = new Account;
        $user = $account->selectByEmail($account_email);
        $password = $user['Password'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;
        $maintenance = $server->maintenance;
        $release = $server->release;
        $isLocalhost = $server->localhost;
        $flashUrl = $server->flashUrl;

        $character = new Character;
        $character->search($account_email, $baseUser);

        if($character->isExist == 0) {
            $body = json_encode([
                'message' => 'Sua conta foi suspensa, entre em contato com o suporte para obter mais informações.',
                'status_code' => 'banned_user'
            ]);
    
            $response->getBody()->write($body);
            return $response;    
        }

        $admin = new Admin;
        
        if($maintenance && empty($admin->selectAdminByEmail($account_email))) {

            if($release == 0) {
                $body = json_encode([                    
                    'redirect' => "serverlist?suv={$suv}&error_code=3",
                    'status_code' => 'server_was_not_opened'
                ]);

                $response->getBody()->write($body);
                return $response;
            } else {
                $body = json_encode([
                    'redirect' => "serverlist?suv={$suv}&error_code=4",
                    'status_code' => 'server_maintenance'
                ]);

                $response->getBody()->write($body);
                return $response;
            }
        }

        $cdn = ($isLocalhost) ? '' : 'cdn.';
        $_hash = base64_encode(strtoupper(md5(
            $account_email.''.$password.''.$_ENV['PUBLIC_KEY']
        )));
        
        $body = json_encode([
            'data' => '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" class= id="7road-ddt-game" codebase="https://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" name="Main" id="Main"><param name="allowScriptAccess" value="always" /><param name="movie" value="//' . $cdn . ''.$_SERVER['SERVER_NAME'].''."/$flashUrl/".'Loading.swf?user='.$account_email.'&key='.$_hash.'&config=//'.$_SERVER['SERVER_NAME'].'/GlobalConfig/'. $decryptServer .'?vui='. $suv .'" /><param name="quality" value="high" /><param name="menu" value="false"><param name="bgcolor" value="#000000" /><param name="FlashVars" value="site=&sitename=&rid=&enterCode=&sex=" /><param name="allowScriptAccess" value="always" /><param name="wmode" value="direct" /><embed wmode="direct" flashvars="site=&sitename=&rid='. md5($account_email) .'&enterCode=&sex=" src="//' . $cdn . ''.$_SERVER['SERVER_NAME'].''."/$flashUrl/".'Loading.swf?user='.$account_email.'&key='.$_hash.'&config=//'.$_SERVER['SERVER_NAME'].'/GlobalConfig/'. $decryptServer .'?vui='. $suv .'" width="1000" height="600" align="middle" quality="high" name="Main" allowscriptaccess="always" type="application/x-shockwave-flash" pluginspage="https://www.macromedia.com/go/getflashplayer"/></object>'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}