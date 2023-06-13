<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;

class AccountController {

    /**
     * Cadastra nova conta
     */
    public function new(Request $request, Response $response) {

        $email             = $_POST['email'];
        $password          = $_POST['password'];
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Account;
        
        $result = $account->create($email, $password, $phone, $ReferenceLocation);
    
        $response->getBody()->write('cadastro');
        return $response->withHeader('Content-Type', 'application/json');
    }

   
    /**
     * Valida e-mail informado
     */
    public function checkEmail(Request $request, Response $response, array $args = []) {

        $email = $args['email'];

        $account = new Account;

        if(empty($account->selectByEmail($email))) {
            $body = ['response' => true];
        } else {
            $body = ['response' => false];
        }

        $response->getBody()->write(json_encode($body));

        return $response->withHeader('Content-Type', 'application/json');
    }


    /**
     * Valida telefone informado
     */
    public function checkPhone(Request $request, Response $response, array $args = []) {

        $phone = $args['phone'];

        $account = new Account;

        if(empty($account->selectByPhone($phone))) {
            $body = ['response' => true];
        } else {
            $body = ['response' => false];
        }

        $response->getBody()->write(json_encode($body));

        return $response->withHeader('Content-Type', 'application/json');
    }
}