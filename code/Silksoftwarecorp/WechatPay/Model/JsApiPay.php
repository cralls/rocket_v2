<?php

/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayException;
use Silksoftwarecorp\WechatPay\Model\Api\Data\JsApiPay as JsApiPayData;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi;
use Silksoftwarecorp\WechatPay\Model\Method\WechatPay as PaymentMethod;


class JsApiPay extends \Silksoftwarecorp\WechatPay\Model\Api\AbstractMethod
{
	protected $curl_timeout = 30;

	public $data = null;


	public function GetOpenid()
	{
		//通过code获得openid
		if (!$this->getRequest()->getParam('code',false)){


			$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
			
			$baseUrl = urlencode($http_type.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
			$url = $this->_CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			return;
		} else {
		    $code = $this->getRequest()->getParam('code', false);
			$openid = $this->getOpenidFromMp($code);
			return $openid;
		}
	}


	public function GetJsApiParameters($UnifiedOrderResult)
	{
		if(!array_key_exists("appid", $UnifiedOrderResult)
		|| !array_key_exists("prepay_id", $UnifiedOrderResult)
		|| $UnifiedOrderResult['prepay_id'] == "")
		{
			throw new WechatPayException("Missing appid or prepay_id");
		}

		$jsapi = new JsApiPayData();
		$jsapi->SetAppid($UnifiedOrderResult["appid"]);
		$timeStamp = time();
		$jsapi->SetTimeStamp("$timeStamp");
		$jsapi->SetNonceStr(WechatPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);


		$jsapi->SetPaySign($jsapi->MakeSign($this->config));
		$parameters = json_encode($jsapi->GetValues());
		return $parameters;
	}


	public function GetOpenidFromMp($code)
	{
		$url = $this->__CreateOauthUrlForOpenid($code);

		$ch = curl_init();
		$curlVersion = curl_version();
		$ua = "WXPaySDK/3.0.9 (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." "
		.$this->config->GetMerchantId();


		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
		$this->config->GetProxy($proxyHost, $proxyPort);
		if($proxyHost != "0.0.0.0" && $proxyPort != 0){
			curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
			curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
		}

		$res = curl_exec($ch);
		$this->logger->debug($res);
		curl_close($ch);
		//取出openid
		$data = json_decode($res,true);
		$this->data = $data;
		$openid = $data['openid'];
		return $openid;
	}


	protected function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}


	public function GetEditAddressParameters()
	{
		$getData = $this->data;
		$data = array();
		$data["appid"] = $this->config->GetAppId();
		$data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$time = time();
		$data["timestamp"] = "$time";
		$data["noncestr"] = WechatPayApi::getNonceStr();
		$data["accesstoken"] = $getData["access_token"];
		ksort($data);
		$params = $this->ToUrlParams($data);
		$addrSign = sha1($params);

		$afterData = array(
			"addrSign" => $addrSign,
			"signType" => "sha1",
			"scope" => "jsapi_address",
			"appId" => $this->config->GetAppId(),
			"timeStamp" => $data["timestamp"],
			"nonceStr" => $data["noncestr"]
		);
		$parameters = json_encode($afterData);
		return $parameters;
	}


	private function _CreateOauthUrlForCode($redirectUrl)
	{

		$urlObj["appid"] = $this->config->GetAppId();
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj['connect_redirect'] = 1;
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}

	
	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = $this->config->GetAppId();
		$urlObj["secret"] = $this->config->GetAppSecret();
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
}
