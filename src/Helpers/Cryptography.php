<?php

namespace App\Zaptank\Helpers;

class Cryptography {

    private $privateKeyCrypt;
    private $publicKeyCrypt;

    public function __construct() {
        $this->privateKeyCrypt = $_ENV['PRIVATE_KEY'];
        $this->publicKeyCrypt = $_ENV['PUBLIC_KEY'];
    }

    public static function safe_b64encode($string = '') {
        $data = base64_encode($string);
        $data = str_replace(['+', '/', '='], ['-', '_', ''], $data);
        return $data;
    }

    public static function safe_b64decode($string = '') {
        $data = str_replace(['-', '_'], ['+', '/'], $string);
        $mod4 = strlen($data) % 4;
        if ($mod4)
        {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function EncryptText(string $message) {
        $KeySK = pack('H*', $this->publicKeyCrypt);
        $KeyPK = pack('H*', $this->privateKeyCrypt);
        $KeysKP = sodium_crypto_box_keypair_from_secretkey_and_publickey($KeySK, $KeyPK);
        $Nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $Text = sodium_crypto_box($message, $Nonce, $KeysKP);
        return $this->safe_b64encode($Nonce . $Text);
    }

    public function DecryptText(string $message) {
        try
        {
            $Resultado = $this->safe_b64decode($message);
            $Text = mb_substr($Resultado, SODIUM_CRYPTO_BOX_NONCEBYTES, null, '8bit');
            $Nonce = mb_substr($Resultado, 0, SODIUM_CRYPTO_BOX_NONCEBYTES, '8bit');
            $KeySK = pack('H*', $this->publicKeyCrypt);
            $KeyPK = pack('H*', $this->privateKeyCrypt);
            $KeysKP = sodium_crypto_box_keypair_from_secretkey_and_publickey($KeySK, $KeyPK);
            $TextEcho = sodium_crypto_box_open($Text, $Nonce, $KeysKP);
            return ($TextEcho ? : '0');
        }
        catch(Exception $e)
        {
            // echo 'Exceção capturada: ', $e->getMessage() , "\n";
        }
    }  
}

