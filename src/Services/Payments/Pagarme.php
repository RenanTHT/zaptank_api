<?php

namespace App\Zaptank\Services\Payments;

class Pagarme {

    public static function generateClient($firstName, $email, $userId) {

        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, "https://api.pagar.me/core/v5/customers");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "{'phones':{'mobile_phone':{'country_code':'55','area_code':'24','number':'992540781'}},'name':'$firstName','email':'$email','code':$userId,'document':'18727053000174','document_type':'CNPJ','type':'company'}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Basic {$_ENV['PAGARME_TOKEN']}"]);
       
        $newclient = curl_exec($curl);
        $curl_response = json_decode(
            curl_exec($curl), true
        );
        return (isset($curl_response['id'])) ? $curl_response['id'] : 0;
    }

    public static function requestGenerateQrcode($base64EncodedReference, $price, $clientId) {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "https://api.pagar.me/core/v5/orders/");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "{'items':[{'amount':$price,'description':'Após o pagamento o produto será enviado instantaneamente, número do pedido: $base64EncodedReference','quantity':1}],'payments':[{'Pix':{'expires_in':3600},'payment_method':'pix'}],'customer_id':'$clientId','antifraud_enabled':false}");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Basic {$_ENV['PAGARME_TOKEN']}"]);
        
        $curl_response = json_decode(
            curl_exec($curl), true
        );
        curl_close($curl);
        return $curl_response;
    }
}