<?php

namespace App\Zaptank\Middlewares\Email;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Models\Email as EmailModel;
use App\Zaptank\Helpers\Date;
use App\Zaptank\Services\Token;

class checkIfEmailChangeTokenIsValid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

        if(!isset($_POST['token']) || empty(trim($_POST['token']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'O token deve ser informado.',
                'status_code' => 'empty_token'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        } else {
            $token = $_POST['token'];

            $emailModel = new EmailModel;
            $emailChangeRequest = $emailModel->selectEmailChangeRequestByToken($token);

            if(empty($emailChangeRequest)) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Seu token de acesso expirou ou não existe, pode ser que você tenha tentado acessar uma página que não tenha permissão.',
                    'status_code' => 'invalid_token'
                ]);

                $response = new Response();
                $response->getBody()->write($body);
                return $response;                
            } else {
                $isChanged = $emailChangeRequest['IsChanged'];
                $expirationTime = date('Y-m-d H:i:s', strtotime('+31 minutes', strtotime($emailChangeRequest['Date'])));

                if($isChanged == 1 || Date::getDate() > $expirationTime) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Seu token de acesso expirou ou não existe, pode ser que você tenha tentado acessar uma página que não tenha permissão.',
                        'status_code' => 'invalid_token'
                    ]);
    
                    $response = new Response();
                    $response->getBody()->write($body);
                    return $response;                    
                }
            }
        }

        return $handler->handle($request);
    }
}