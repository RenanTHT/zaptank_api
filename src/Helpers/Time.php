<?php

namespace App\Zaptank\Helpers;

use \DateTime;
use \DateInterval;

date_default_timezone_set('America/Sao_Paulo');

class Time {

    public static function get() {
        return date('H:i:s', time());
    }

    public static function differenceInSecondsBetweenTwoHours($start, $end) {

        $startTimestamp = strtotime($start);
        $endTimestamp = strtotime($end);
    
        if ($startTimestamp === false || $endTimestamp === false) {
            return false;
        }
    
        if ($startTimestamp > $endTimestamp) {
            $endTimestamp += 24 * 60 * 60;
        }
    
        $difference = $endTimestamp - $startTimestamp;
    
        return $difference;
    }

    /*public static function convertTimeToMinutes(DateInterval $dateInterval) {
        $hours = $dateInterval->h;
        $minutes = $dateInterval->i;
    
        return $total_minutes = $hours * 60 + $minutes;
    }*/
}