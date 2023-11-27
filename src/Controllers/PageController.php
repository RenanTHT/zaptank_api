<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Zaptank\Models\Admin;
use App\Zaptank\Models\Account;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Ticket;
use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\VirtualBag;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\Date;

class PageController {

    public function serverList(Request $request, Response $response, array $args) :Response {

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
        
        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $account = new Account;
        $user = $account->selectByEmail($account_email);

        if($user['IsBanned']) {
            $body = json_encode([
                'message' => '',
                'status_code' => 'banned_user'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        if($decryptServer == false) {
            $response = new Response();
            return $response->withStatus(500);  
        }

        $server = new Server;
        $server->search($decryptServer);
        $release = $server->release;

        $alerts = [];

        $admin = new Admin;

        if(!empty($admin->selectAdminByEmail($account_email))) {
            $ticket = new Ticket;
            $ticketCount = $ticket->getCountOfOpenTickets();
            $alerts['ticket'] = [
                'show' => true,
                'data' => $ticketCount
            ];
        } else {
            $alerts['ticket'] = [
                'show' => false,
                'data' => 0
            ];
        }

        $invoice = new Invoice;
        if($invoice->selectRechargeCountWithRefund($decryptServer, $account_email) > 0 && $release) {
            $alerts['chargeback'] = [
                'show' => true
            ];
        } else {
            $alerts['chargeback'] = [
                'show' => false
            ];
        }

        $virtualBag = new VirtualBag;
        $itemCount = $virtualBag->selectUnusedBackpackItemCount($decryptServer, $account_email);
        if($itemCount > 0) {
            $alerts['backpack'] = [
                'show' => true,
                'data' => $itemCount
            ];
        } else {
            $alerts['backpack'] = [
                'show' => false,
                'data' => 0
            ];
        }

        $info = [
            'verified' => boolval($user['VerifiedEmail']),
            'created_at' => Date::formatDate($user['CreateDate'])
        ];

        if($user['Opinion'] == 0) {
            $alerts['survey'] = [
                'show' => true
            ];
        } else {
            $alerts['survey'] = [
                'show' => false
            ];
        }
        
        $alerts['promotion'] = [
            'show' => boolval($user['IsFirstCharge'])
        ];

        $body = json_encode([
            'info' => $info,
            'alerts' => $alerts,
            'status_code' => 'list_info'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}