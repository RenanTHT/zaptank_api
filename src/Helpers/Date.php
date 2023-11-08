<?php

namespace App\Zaptank\Helpers;

use \DateTime;
use \DateInterval;

class Date {

    public static function getDate($pattern = 'Y-m-d H:i:s') :string {
        return date($pattern);
    }

    public static function formatDate($date, $pattern = 'd-m-Y') :string {
        return date($pattern, strtotime($date));
    }

    public static function difference($start, $end) :DateInterval {

        $start_time = DateTime::createFromFormat('Y-m-d H:i:s', $start);
        $end_time = DateTime::createFromFormat('Y-m-d H:i:s', $end);

        if ($end_time->format('H') == '00' && $start_time > $end_time) {
            $end_time->modify('+1 day');
        }        
        return $interval = $start_time->diff($end_time);
    }
}