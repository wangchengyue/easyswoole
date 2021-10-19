<?php
namespace App\Common;

if (!function_exists('helloEasySwoole')) {
    function helloEasySwoole()
    {
        echo 'Hello EasySwoole!';
    }
}

if(!function_exists('getIP')){
    function getIP(){
        return __FUNCTION__;
    }
}

if(!function_exists('get_curl')){
    function get_curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        return $result;
    }
}
