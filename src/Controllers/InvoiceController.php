<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\Vip;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Character;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class InvoiceController {

    public function new(Request $request, Response $response, array $args) :Response {

        if (
            (!isset($_POST['full_name']) || empty(trim($_POST['full_name']))) ||
            (!isset($_POST['phone']) || empty(trim($_POST['phone']))) ||
            (!isset($_POST['email']) || empty(trim($_POST['email']))) ||
            (!isset($_POST['vip_package']) || empty(trim($_POST['vip_package'])))
        ) {
            $body = json_encode([
                'success' => false,
                'message' => 'preencha todos os campos.',
                'status_code' => 'empty_fields'
            ]);

            $response->getBody()->write($body);
            return $response;
        } else {
            $full_name = $_POST['full_name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $vip_package = $_POST['vip_package'];
        }

        $vip = new Vip;
        $vipPackageDetails = $vip->selectById($vip_package);

        if(empty($vipPackageDetails)) {
            $body = json_encode([
                'success' => false,
                'message' => 'O pacote vip selecionado não existe.',
                'status_code' => 'unknown_vip_package'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;
        
        $account_email = $payload['email'];
        $status = "Pendente";
        $method = "Fatura";
        $price = $vipPackageDetails['ValuePrice'];

        $invoice = new Invoice;
        $invoice->store($full_name, $phone, $account_email, $vip_package, $method, $price, $status, $serverId);

        $newInvoiceId = $invoice->selectLastInvoice($account_email);
        $newInvoiceEncryptedId = $cryptography->EncryptText($newInvoiceId);

        $redirect = "?page=invoice&show=$newInvoiceEncryptedId&server=$suv";

        $body = json_encode([
            'success' => true,
            'message' => 'fatura criado com sucesso!',
            'status_code' => 'invoice_created',
            'data' => [
                'redirect' => $redirect
            ]
        ]);

        $response->getBody()->write($body);
        return $response;
    }

    public function get(Request $request, Response $response, array $args) :Response {

        if(!isset($_GET['invoice_id']) || empty(trim($_GET['invoice_id']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Erro interno, parâmetro de requisição invoice_id não foi informado.',
                'status_code' => 'unknow_invoice'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];
        $encryptedInvoiceId = $_GET['invoice_id'];
                
        $cryptography = new Cryptography;

        $invoiceId = $cryptography->DecryptText($encryptedInvoiceId);        
        $decryptServer = $cryptography->DecryptText($suv);

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $invoice = new Invoice;
        $invoiceDetails = $invoice->selectBy_InvoiceId_And_Email($invoiceId, $account_email);

        if(empty($invoiceDetails)) {
            $body = json_encode([
                'message' => 'Essa fatura não existe.',
                'status_code' => 'unknow_invoice'
            ]);
    
            $response->getBody()->write($body);
            return $response; 
        }

        $server = new Server;
        $server->search($decryptServer);
        $baseUser = $server->baseUser;

        $character = new Character;
        $character->search($account_email, $baseUser);
        $characterId = $character->Id;
        $nickname = $character->nickName;

        $body = json_encode([
            'data' => [
                'invoice' => [
                    'status' => $invoiceDetails['Status'],
                    'price' => $invoiceDetails['Price'],
                    'qrcode_openpix' => $invoiceDetails['PixDataImage'],
                    'key_openpix' => $invoiceDetails['KeyRef'],
                    'qrcode_picpay' => $invoiceDetails['PicPayQrCode']
                ],
                'character' => [
                    'nickname' => $nickname
                ]
            ]
        ]);

        $response->getBody()->write($body);
        return $response;        
    }
}