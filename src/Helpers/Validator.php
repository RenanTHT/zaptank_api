<?php

namespace App\Zaptank\Helpers;

class Validator {

    public static function validateEmail($email) :bool {

        $extension = strrchr($email, '@');
        $whitelist = array('gmail.com', 'outlook.com', 'hotmail.com', 'hotmail.com.br', 'yahoo.com','yahoo.com.br', 'live.com', 'icloud.com', 'outlook.pt', 'outlook.com.br', 'icloud.com.br', 'qq.com');
        $ex = explode('@', $email);

        return !(empty($extension) || !in_array(array_pop($ex), $whitelist));
    }
}