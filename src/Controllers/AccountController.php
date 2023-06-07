<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Account as Model;

class AccountController {

    public function new(Request $request, Response $response) {

        $email             = $_POST['email'];
        $password          = $_POST['password'];
        $phone             = $_POST['phone'];
        $ReferenceLocation = $_POST['ReferenceLocation'];
    
        $account = new Model;
        
        $result = $account->store($email, $password, $phone, $ReferenceLocation);
    
        $response->getBody()->write($result);
        return $response->withHeader('Content-Type', 'application/json');
    }
}