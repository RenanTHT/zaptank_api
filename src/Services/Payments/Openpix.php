<?php

namespace App\Zaptank\Services\Payments;

class Openpix {

    public static function requestGenerateQrcode($payload) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.openpix.com.br/api/v1/charge?return_existing=true");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: {$_ENV['OPENPIX_TOKEN']}"]);
        
        $curl_response = json_decode(
            curl_exec($curl), true
        );
        curl_close($curl);
        return $curl_response;
    }

    public static function generateClient($firstName, $email) {

        $clientData = json_encode(array(
            "name" => $firstName,
            "email" => $email
        ));
    
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.openpix.com.br/api/v1/customer");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $clientData);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: {$_ENV['OPENPIX_TOKEN']}"]);
        
        $curl_response = json_decode(
            curl_exec($curl), true
        );
        curl_close($curl);
        return $curl_response;
    }
}