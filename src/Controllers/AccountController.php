<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account;

class AccountController {

    /**
     * Cadastra dados no database
     */
    public function new(Request $request, Response $response) {

        $email             = $_POST['email'];
        $password          = $_POST['password'];
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Account;
        
        $result = $account->create($email, $password, $phone, $ReferenceLocation);
    
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    }


    /**
     * Valida e-mail informado
     */
    public function checkEmail(Request $request, Response $response, array $args = []) {

        $email = $_GET['email'];

        $account = new Account;

        $emailExists = empty($account->selectByEmail($email)) ? true : false;

        $response->getBody()->write(json_encode($emailExists));

        return $response->withHeader('Content-Type', 'application/json');
    }


    /**
     * Valida telefone informado
     */
    public function checkPhone(Request $request, Response $response, array $args = []) {

        $phone = $_GET['phone'];

        $account = new Account;

        $phoneExists = empty($account->selectByPhone($phone)) ? true : false;

        $response->getBody()->write(json_encode($phoneExists));

        return $response->withHeader('Content-Type', 'application/json');
    }
}