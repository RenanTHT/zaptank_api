<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;
use App\Zaptank\Helpers\Cryptography;

class EmailController {

    public function unsubscribe(Request $request, Response $response, array $args) :Response {

        $cryptography = new Cryptography;

        $email = $cryptography->DecryptText($args['email_token']);

        $account = new Account;
        $user = $account->selectByEmail($email);

        if(empty($user)) {
            $body = json_encode([
                'success' => false,
                'message' => 'Email não existe na base de dados.',
                'status_code' => 'unknow_email'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $account->updateBadMail($email, $badMail = 1);
        $account->updateVerifiedEmail($email, $verifiedEmail = 0);

        $body = json_encode([
            'success' => true,
            'message' => 'Seu e-mail foi removido da nossa lista.',
            'data' => $user
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}