<?php

namespace App\Zaptank\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use App\Zaptank\Helpers\Date;
use App\Zaptank\Services\Token;
use App\Zaptank\Database;

class checkIfEmailChangeTokenIsValid {

    public function __invoke(Request $request, RequestHandler $handler) :Response {

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
            $stmt = $database->get()->prepare("SELECT TOP 1 * FROM {$_ENV['BASE_SERVER']}.dbo.change_email WHERE userID = :id and token = :token and IsChanged = 0 ORDER BY Date");
            $stmt->bindParam(':id', $uid);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(empty($result)) {
                $body = json_encode([
                    'success' => false,
                    'message' => 'Seu token de acesso expirou ou não existe, pode ser que você tenha tentado acessar uma página que não tenha permissão.',
                    'status_code' => 'invalid_token'
                ]);

                $response = new Response();
                $response->getBody()->write($body);
                return $response;                
            } else {
                $created_at = date('Y-m-d H:i:s', strtotime($result['Date']));
                $expires = Date::difference($created_at, Date::getDate());

                if($expires->i >= 30 || $expires->h >= 1 || $expires->d >= 1 || $expires->m >= 1 || $expires->y >= 1) {
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