<?php

namespace App\Zaptank\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Services\Token;
use App\Zaptank\Database;

class checkIfEmailChangeTokenIsValid {

    public function __invoke(Request $request, RequestHandler $handler) {

        $token = $_POST['token'];

        if(empty($token)) {
            $body = json_encode([
                'success' => false,
                'message' => 'O token deve ser informado.',
                'status_code' => 'empty_token'
            ]);

            $response = new Response();
            $response->getBody()->write($body);
            return $response;
        } else {
            $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

            $tokenService = new Token;
            $payload = $tokenService->validate($jwt);
    
            $uid = $payload['sub'];

            $database = new Database;

            $stmt = $database->get()->prepare("SELECT Date FROM {$_ENV['BASE_SERVER']}.dbo.change_email WHERE userID = :id and token = :token and IsChanged = 0");
            $stmt->bindParam('id', $uid);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            // observação: Validar tempo de expiração de token
            if(empty($result)) {
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

        return $handler->handle($request);
    }
}