<?php

namespace App\Zaptank\Controllers\Payments;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\Server;
use App\Zaptank\Models\Vip;
use App\Zaptank\Models\Character;
use App\Zaptank\Models\Account;
use App\Zaptank\Models\ChargeMoney;

use App\Zaptank\Services\Payments\Picpay;
use App\Zaptank\Services\Email;

use App\Zaptank\Helpers\CurlRequest;

class PaymentNotificationController {
    
    public function picpayNotification(Request $request, Response $response) :Response {

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/json');
        http_response_code(200);

        $content = json_decode(
            file_get_contents('php://input')
        );

        if(!empty($content) && isset($content->referenceId)) {
            $referenceId = $content->referenceId;
        } else if(isset($_GET['referenceId']) && $_GET['referenceId'] != null) {
            $referenceId = $_GET['referenceId'];
        } else {
            $body = json_encode(false);
            $response->getBody()->write($body);
            return $response;
        }

        $request_status = Picpay::requestPaymentStatus($referenceId);

        if(!isset($request_status->status)) {
            $body = json_encode(false);
            $response->getBody()->write($body);
            return $response;
        }
        
        $status = $request_status->status;

        if($status == 'paid' || $status == 'completed') {

            $invoiceId = base64_decode($referenceId);

            $invoice = new Invoice;
            $invoiceDetails = $invoice->selectById($invoiceId);

            $vip_package = $invoiceDetails['PacoteID'];
            $account_email = $invoiceDetails['UserName'];
            $price = $invoiceDetails['Price'];
            $invoiceStatus = $invoiceDetails['Status'];
            $serverId = $invoiceDetails['ServerID'];

            if($invoiceStatus == 'Pendente') {

                $server = new Server;

                $server->search($serverId);
                $baseUser = $server->baseUser;
                $areaId = $server->areaId;
                $questUrl = $server->questUrl;

                $vip = new Vip;
                $coupons = $vip->selectVipItemCountByPackageIdAndTemplateId($vip_package, $templateId = '-200');

                $character = new Character;
                $character->search($account_email, $baseUser);
                $characterId = $character->Id;
                $nickName = $character->nickName;

                $account = new Account;
                $user = $account->selectByEmail($account_email);
                $VerifiedEmail = $user['VerifiedEmail'];
                $isFirstCharge = $user['IsFirstCharge'];

                if($isFirstCharge) {
                    $percentage = 15;
                    $additionalCoupons = ($percentage / 100) * $coupons;
                    $coupons += $additionalCoupons;
                    $account->updateIsFirstCharge($account_email);
                }

                $invoice->updateStatus($invoiceId, $status = 'Aprovada');
                $invoice->updateMethod($invoiceId, $method = 'PicPay');

                $chargeMoney = new ChargeMoney;
                $chargeMoney->store($baseUser, $account_email, $coupons, $date = date('d-m-Y h:i:s'), $canUse = 1, $method = 'PicPay', $price);

                CurlRequest::post("{$questUrl}/UpdateMailByUserID.ashx?UserID={$characterId}&AreaID={$areaId}&key=LizardGamesTqUserZap500K777");

                if($VerifiedEmail == 1) {
                    $emailService = new Email;
                    $emailService->send(
                        $subject = 'DDTank - Comprovante de Recarga!',
                        $body = '<style>@import url(https://fonts.googleapis.com/css?family=Roboto); body{font-family: "Roboto", sans-serif; font-size: 48px;}</style><table cellpadding="0" cellspacing="0" border="0" style="padding:0;margin:0 auto;width:100%;max-width:620px"> <tbody> <tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr><tr> <td style="padding:0;margin:0;font-size:1px">&nbsp;</td><td style="padding:0;margin:0" width="590"> <span class="im"> <table width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr style="background-color:#fff"> <td style="padding:11px 23px 8px 15px;float:right;font-size:12px;font-weight:300;line-height:1;color:#666;font-family:" Proxima Nova",Helvetica,Arial,sans-serif"> <p style="float:right">' . $account_email . '</p></td></tr></tbody> </table> <table bgcolor="#d65900" width="100%" cellspacing="0" cellpadding="0" border="0"> <tbody> <tr> <td height="0"></td></tr><tr> <td align="center" style="display:none"> <img width="90" style="width:90px;text-align:center"> </td></tr><tr> <td height="0"></td></tr><tr> <td class="m_-5336645264442155576title m_-5336645264442155576bold" style="padding:63px 33px;text-align:center" align="center"> <span class="m_-5336645264442155576mail__title" style=""> <h1> <font color="#ffffff">Você realizou uma recarga no valor de R$' . $price . ' sua recarga foi aprovada e entregue. Número do Pedido: ' . $referenceId . '</b> </font> </h1> </span> </td></tr><tr> <td style="text-align:center;padding:0"> <div id="m_-5336645264442155576responsive-width" class="m_-5336645264442155576responsive-width" width="78.2% !important" style="width:77.8%!important;margin:0 auto;background-color:#fbee00;display:none"> <div style="height:50px;margin:0 auto">&nbsp;</div></div></td></tr></tbody> </table> </span> <div id="m_-5336645264442155576div-table-wrapper" class="m_-5336645264442155576div-table-wrapper" style="text-align:center;margin:0 auto"> <table class="m_-5336645264442155576main-card-shadow" bgcolor="#ffffff" align="center" border="0" cellpadding="0" cellspacing="0" style="border:none;padding:48px 33px 0;text-align:center"> <tbody> <td align="center"><p class="m_-5336645264442155576mail__text-card m_-5336645264442155576bold" style="text-decoration:none;font-family:"Proxima Nova",Arial,Helvetica,sans-serif;text-align:center;line-height:16px;max-width:390px;width:100%;margin:0 auto 44px;font-size:14px;color:#999">O ZapTank enviou este e-mail pois você optou por recebê-lo ao cadastrar-se no site.</p></td></tr></tbody> </table> </div></td><td style="padding:0;margin:0;font-size:1px">&nbsp;</td></tr><tr> <td colspan="3" style="padding:0;margin:0;font-size:1px;height:1px" height="1">&nbsp;</td></tr></tbody></table><small class="text-muted"> <?php setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "pt_BR.utf-8", "portuguese"); date_default_timezone_set("America/Sao_Paulo"); echo strftime("%A, %d de %B de %Y", strtotime("today"));?> </small>',
                        $altBody = 'DDTank - Comprovante de Recarga!',
                        $account_email
                    );
                }

                $body = json_encode(true);
                $response->getBody()->write($body);
                return $response;
            } else {
                $body = json_encode(false);
                $response->getBody()->write($body);
                return $response;
            }
        } else if($status == 'analysis' || $status == 'refunded' || $status == 'chargeback') {
            $file = "./logs/payments/picpay.txt";
            file_put_contents($file, $request_status . PHP_EOL, FILE_APPEND | LOCK_EX);

            $body = json_encode(false);
            $response->getBody()->write($body);
            return $response;
        } else {
            $body = json_encode(false);
            $response->getBody()->write($body);
            return $response;
        }
    }
   
    public function pagarmeNotification(Request $request, Response $response) :Response {

        $body = json_encode([
            'message' => 'Notificação do pagarme.'
        ]);

        $response->getBody()->write($body);
        return $response;
    }
}