<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\Vip;
use App\Zaptank\Models\Server;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;

class InvoiceController {

    public function new(Request $request, Response $response, array $args) :Response {

        if(empty($_POST['full_name']) || empty($_POST['phone']) || empty($_POST['email']) || empty($_POST['vip_package'])) {
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
}