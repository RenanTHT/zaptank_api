<?php

namespace App\Zaptank\Controllers\Payments;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PaymentNotificationController {
    
    public function picpayNotification(Request $request, Response $response) :Response {

        $body = json_encode([
            'message' => 'Notificação do picpay.'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
   
    public function pagarmeNotification(Request $request, Response $response) :Response {

        $body = json_encode([
            'message' => 'Notificação do pagarme.'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}