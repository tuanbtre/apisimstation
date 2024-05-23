<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
	public function index(){
		$url = 'https://api-flow-ts.billionconnect.com/Flow/saler/2.0/invoke';
		$app_key='simstationtest';
		$app_secret='48f1c6c62713467797fdee91f6ee60a0';
		$params = array();
		$params['tradeTime'] = date('Y-m-d H:i:s');
		$params['tradeType'] = 'F001';
		$trade_data = array();
		$trade_data['salesMethod'] = '1';
		$params['tradeData'] = $trade_data;
		$sign = $this->getSign($app_secret, $params);
		$response = Http::withHeaders([
			'Content-Type' => 'application/json',
			'x-channel-id' => $app_key,
			'x-sign-method' => 'md5',
			'x-sign-value' => $sign
		])->acceptJson()->post($url);
		dd($response);
	}
	public function getSign($app_secret, $params) {    
		return md5($app_secret.json_encode($params, JSON_UNESCAPED_UNICODE));
	}
}
