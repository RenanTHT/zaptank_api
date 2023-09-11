<?php

namespace App\Zaptank\Services\Payments;

class Picpay {

    public static function requestGenerateQrcode($base64EncodedReference, $price, $firstName, $document, $phone, $account_email) {

        $returnUrl = "https://redezaptank.com.br/selectserver?page=purchase&ref=$base64EncodedReference"; // analisar
        $callbackUrl = "https://redezaptank.com.br/Payments/picpay?referenceId=$base64EncodedReference"; // trocar
        $expiresAt = date('Y-m-d', strtotime("+3 day", strtotime(date('Y-m-d'))));

        $data = ["referenceId" => $base64EncodedReference, "callbackUrl" => $callbackUrl, "returnUrl" => $returnUrl, "value" => $price, "expiresAt" => $expiresAt, "buyer" => ["firstName" => $firstName, "lastName" => '', "document" => $document, "email" => $account_email, "phone" => $phone]];
        
        $curl = curl_init('https://appws.picpay.com/ecommerce/public/payments');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['x-picpay-token: ' . $_ENV['PICPAY_TOKEN']]);

        $curl_response = json_decode(
            curl_exec($curl)
        );
        curl_close($curl);
        return $curl_response;
    }

    public static function requestPaymentStatus($referenceId) {

        $url = "https://appws.picpay.com/ecommerce/public/payments/{$referenceId}/status";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Picpay-Token: ' . $_ENV['PICPAY_TOKEN']]);
        
        $curl_response = json_decode(
            curl_exec($curl)
        );
        
        if(curl_errno($curl)) {
            $file = "./logs/logspicpay.txt";
            file_put_contents($file, curl_error($curl) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return;
        }
        curl_close($curl);
        return $curl_response;
    }
}