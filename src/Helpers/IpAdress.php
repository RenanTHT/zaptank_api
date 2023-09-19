<?php

namespace App\Zaptank\Helpers;

class IpAdress {

    public static function getUserIp() {
        return $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    }
}