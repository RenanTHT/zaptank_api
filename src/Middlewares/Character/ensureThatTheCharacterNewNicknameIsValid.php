<?php

namespace App\Zaptank\Middlewares\Character;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ensureThatTheCharacterNewNicknameIsValid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        if(!isset($_POST['newnick']) || empty(trim($_POST['newnick']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Você não digitou o nome do personagem.',
                'status_code' => 'empty_nickname'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response; 
        }
    
        $new_nickname = $_POST['newnick'];

        $specialChars = array(".", ",", "?", "!", "'", "\\", ":", "(", ")", "/", '"', ";", "-", "+", "<", ">", "%", "~", "€", "$", "[", "]", "{", "}", "@", "&", "#", "*", "„");
        if(strpbrk($new_nickname, implode('', $specialChars))) {
            $body = json_encode([
                'success' => false,
                'message' => 'O nome do personagem contém caracteres especiais.',
                'status_code' => 'invalid_special_characters'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        }
    
        if(mb_strlen($new_nickname) < 3 || mb_strlen($new_nickname) > 16) {
            $body = json_encode([
                'success' => false,
                'message' => 'O Nome do seu personagem deve ter entre 3 a 16 caracteres.',
                'status_code' => 'invalid_special_characters'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        }        
          
        return $handler->handle($request);
    }
}