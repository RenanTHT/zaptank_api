<?php

namespace App\Zaptank\Helpers;

class CurlRequest {
		
	public static function get(string $url) {
		
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);		

		$response = curl_exec($curl);
		curl_close($curl);
		
		return json_decode($response, true);		
	}
	
    
    public static function post(string $url) {
		
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);		

		$response = curl_exec($curl);
		curl_close($curl);
		
		return json_decode($response, true);		
	}
}