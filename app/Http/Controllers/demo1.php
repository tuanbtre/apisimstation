<?php

function getSign($app_secret, $params) {    
    return md5($app_secret.json_encode($params, JSON_UNESCAPED_UNICODE));
}


function sent_request($url, $app_key, $app_secret, $params)
{
    $sign = getSign($app_secret, $params);
    $header = array(
        'Content-Type:application/json',
        'x-channel-id:'.$app_key,
        'x-sign-method:md5',
        'x-sign-value:'.$sign
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    $retval = curl_exec($ch);
    curl_close($ch);

    return json_decode($retval);
}


$url = 'https://api-flow-ts.billionconnect.com/Flow/saler/2.0/invoke';
$app_key='simstationtest';
$app_secret='48f1c6c62713467797fdee91f6ee60a0';

$params = array();
$params['tradeTime'] = '2017-02-10 11:11:11';
$params['tradeType'] = 'F001';
$trade_data = array();
$trade_data['salesMethod'] = '1';
$params['tradeData'] = $trade_data;

$res = sent_request($url, $app_key, $app_secret, $params);
print_r($res);