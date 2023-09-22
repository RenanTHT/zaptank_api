<?php

namespace App\Zaptank\Controllers\Payments;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Zaptank\Models\Invoice;
use App\Zaptank\Models\Vip;
use App\Zaptank\Services\Token;
use App\Zaptank\Services\Payments\Picpay;
use App\Zaptank\Services\Payments\Pagarme;
use App\Zaptank\Helpers\Cryptography;
use App\Zaptank\Helpers\CurlRequest;

class PaymentController {

    public function newPixPayment(Request $request, Response $response, array $args) :Response {

        $jwt = explode(' ', $request->getHeader('Authorization')[0])[1];

        $gateway = $args['gateway'];

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

        $cryptography = new Cryptography;
        $invoiceId = $cryptography->DecryptText($encryptedInvoiceId);

        $token = new Token;
        $payload = $token->decode($jwt);

        $uid = $payload['sub'];
        $account_email = $payload['email'];

        $invoice = new Invoice;

        if($invoice->selectInvoiceCountByIdAndUser($invoiceId, $account_email) == 0) {
            $body = json_encode([
                'success' => false,
                'message' => 'Essa fatura não existe.',
                'status_code' => 'unknow_invoice'
            ]);

            $response->getBody()->write($body);
            return $response;
        }
        
        switch($gateway) {
            case 'picpay':
                $invoiceDetails = $invoice->selectById($invoiceId);  
                
                $base64EncodedReference = base64_encode($invoiceId);
                $price = $invoiceDetails['Price'];

                $firstName = $invoiceDetails['Name'];
                $document = "#";
                $phone = $invoiceDetails['Number'];
                
                $qrcode = Picpay::requestGenerateQrcode($base64EncodedReference, $price, $firstName, $document, $phone, $account_email);

                if(!empty($qrcode->qrcode->base64)) {
            
                    $picpayLink = $qrcode->paymentUrl;
                    $picpayQrCode = $qrcode->qrcode->base64;
                    $invoice->updatePicpayQrCode($invoiceId, $picpayQrCode);
                    $invoice->updatePicpayLink($invoiceId, $picpayLink);
        
                    $body = json_encode([
                        'success' => true,
                        'status_code' => 'picpay_qrcode_created'
                    ]);
            
                    $response->getBody()->write($body);
                    return $response;
                } else {                    
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Ocorreu um erro interno, por favor tente novamente mais tarde...',
                        'status_code' => 'picpay_qrcode_not_created'
                    ]);
            
                    $response->getBody()->write($body);
                    return $response;
                }
                break;
            case 'pagarme':
                $invoiceDetails = $invoice->selectById($invoiceId);  

                $firstName = $invoiceDetails['Name'];
                $price = str_replace(".", "", $invoiceDetails['Price']);

                $clientId = Pagarme::generateClient($firstName, $account_email, $uid);

                if($clientId == 0) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Ocorreu um erro interno, por favor tente novamente mais tarde...',
                        'status_code' => 'unable_to_create_user'
                    ]);
    
                    $response->getBody()->write($body);
                    return $response;
                }

                $base64EncodedReference = base64_encode($invoiceId);

                $qrcode = Pagarme::requestGenerateQrcode($base64EncodedReference, $price, $clientId);

                if(!isset($qrcode['id'])) {
                    $body = json_encode([
                        'success' => false,
                        'message' => 'Não foi possível gerar a chave aleatória, gere um novo QrCode.',
                        'status_code' => 'pagarme_qrcode_not_created'
                    ]);
    
                    $response->getBody()->write($body);
                    return $response;                    
                }

                $orderNumber = $qrcode->id;
                $referenceKey = $qrcode->charges[0]->last_transaction->qr_code;
                $qrcodeUrl = $qrcode->charges[0]->last_transaction->qr_code_url;
                $qrcodeImageUrl = 'data:image/jpg;base64,' . base64_encode(CurlRequest::get($qrcodeUrl));

                $invoice->updateReferenceKey($invoiceId, $referenceKey);
                $invoice->updatePixDataImage($invoiceId, $qrcodeImageUrl);
                $invoice->updateOrderNumber($invoiceId, $orderNumber);
                $invoice->updateMethodByInvoiceId($invoiceId, $method = 'PIX');

                $body = json_encode([
                    'success' => true,
                    'message' => 'Qrcode gerado com sucesso!',
                    'status_code' => 'qr_code_was_generated'
                ]);

                $response->getBody()->write($body);
                return $response;
                break;
            default:
                $body = json_encode([
                    'success' => false,
                    'message' => 'Forma de pagamento inválida.',
                    'status_code' => 'invalid_payment_method'
                ]);

                $response->getBody()->write($body);
                return $response;
                break;
        }
    }
}