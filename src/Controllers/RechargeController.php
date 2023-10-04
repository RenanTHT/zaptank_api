<?php

namespace App\Zaptank\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\CharacterMail;
use App\Zaptank\Services\Token;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\CurlRequest;

class RechargeController {

    public function checkChargebackDetails(Request $request, Response $response, array $args) :Response {
     
        if($_ENV['ENABLE_CHARGEBACK'] == false) {
            $body = json_encode([
                'enable_chargeback' => false,
                'content' => "A coleta estará disponível no dia {$_ENV['CHARGEBACK_AVAILABLE_AT']}"
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;

        $invoice = new Invoice;
        $refund = $invoice->selectBy_ServerId_And_Email($serverId, $account_email);

        if(empty($refund)) {
            $body = json_encode([
                'enable_chargeback' => true,
                'collect_chargeback' => false,
                'content' => 'Você não possui nenhuma fatura para mostrar.'
            ]);

            $response->getBody()->write($body);
            return $response;
        } else {    
            foreach($refund as $key => $invoice) {
                $refund[$key]['id'] = $cryptography->EncryptText($invoice['id']);
                $refund[$key]['recharge_date'] = date('d-m-Y', strtotime($refund[$key]['recharge_date']));
            }

            $body = json_encode([
                'enable_chargeback' => true,
                'collect_chargeback' => true,
                'data' => $refund
            ]);

            $response->getBody()->write($body);
            return $response;
        }
    }

    public function collectChargeback(Request $request, Response $response, array $args) :Response {

        if(!isset($_POST['invoice_id']) || empty(trim($_POST['invoice_id']))) {
            $body = json_encode([
                'success' => false,
                'message' => 'Erro interno, parâmetro de requisição invoice_id não foi informado.',
                'status_code' => 'unknow_invoice'
            ]);

            $response->getBody()->write($body);
            return $response;
        }

        $encryptedInvoiceId = $_POST['invoice_id'];

        $suv = $args['suv'];
        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $cryptography = new Cryptography;
        $decryptServer = $cryptography->DecryptText($suv);
        $invoiceId = $cryptography->DecryptText($encryptedInvoiceId);

        $server = new Server;
        $server->search($decryptServer);
        $serverId = $server->Id;
        $baseUser = $server->baseUser;
        $areaId = $server->areaId;
        $questUrl = $server->questUrl;

        $token = new Token;
        $payload = $token->decode($jwt);
        $account_email = $payload['email'];

        $character = new Character;
        $character->search($account_email, $baseUser);
        $characterId = $character->Id;
        $nickname = $character->nickName;

        $invoice = new Invoice;
        $invoiceDetails = $invoice->selectBy_ServerId_InvoiceId_And_Email($serverId, $invoiceId, $account_email);

        $vip_package = $invoiceDetails['PacoteID'];
        $price = $invoiceDetails['Price'];
        $invoiceStatus = $invoiceDetails['Status'];

        switch($vip_package) {
            case "1":
                $templateId = 1128000;
                break;
            case "2":
                $templateId = 1128001;
                break;
            case "3":
                $templateId = 1128002;
                break;
            case "4":
                $templateId = 1128003;
                break;
            case "5":
                $templateId = 1128004;
                break;
            case "6":
                $templateId = 1128005;
                break;
            case "7":
                $templateId = 1128006;
                break;
            case "8":
                $templateId = 1128007;
                break;
            case "9":
                $templateId = 1128008;
                break;
            case "10":
                $templateId = 1128009;
                break;
            default:
                $body = json_encode([
                    'success' => false,
                    'message' => 'Não foi possível processar sua solicitação, caso o problema persista abra um ticket na central do jogo.',
                    'status_code' => 'internal_error_vip_package_does_not_exist'
                ]);

                $response->getBody()->write($body);
                return $response;
                break;
        }

        $characterMail = new CharacterMail;
        $characterMail->SP_Admin_SendUserItem($characterId, $nickname, $templateId, $count = 1, $baseUser);

        CurlRequest::post("{$questUrl}/UpdateMailByUserID.ashx?UserID={$characterId}&AreaID={$areaId}&key=LizardGamesTqUserZap500K777");
        $invoice->updateIsChargebackToFalse($invoiceId);

        $body = json_encode([
            'success' => true,
            'message' => 'Enviado com sucesso!',
            'status_code' => 'coupons_sent_successfully'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}