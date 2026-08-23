<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api;

use Silksoftwarecorp\WechatPay\Model\Api\WechatPayException;
use Silksoftwarecorp\WechatPay\Model\Api\Data\Results;
use Silksoftwarecorp\WechatPay\Model\Api\Data\NotifyResults;
class WechatPayApi{

	public static function unifiedOrder($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WechatPayException("out_trade_no can not be empty!");
		}else if(!$inputObj->IsBodySet()){
			throw new WechatPayException("body can not be empty!");
		}else if(!$inputObj->IsTotal_feeSet()) {
			throw new WechatPayException("total_fee can not be empty!");
		}else if(!$inputObj->IsTrade_typeSet()) {
			throw new WechatPayException("trade_type can not be empty!");
		}

		if($inputObj->GetTrade_type() == "JSAPI" && !$inputObj->IsOpenidSet()){
			throw new WechatPayException("Openid is required when trade_type is JSAPI.");
		}
		if($inputObj->GetTrade_type() == "NATIVE" && !$inputObj->IsProduct_idSet()){
			throw new WechatPayException("product_id is required when trade_type is NATIVE.");
		}

		if(!$inputObj->IsNotify_urlSet() && $config->GetNotifyUrl() != ""){
			$inputObj->SetNotify_url($config->GetNotifyUrl());
		}

		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function orderQuery($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WechatPayException("out_trade_no and transaction_id should fill in at least one");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function closeOrder($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/closeorder";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet()) {
			throw new WechatPayException("out_trade_no is required.");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function refund($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WechatPayException("out_trade_no and transaction_id should fill in at least one");
		}else if(!$inputObj->IsOut_refund_noSet()){
			throw new WechatPayException("out_refund_no is required.");
		}else if(!$inputObj->IsTotal_feeSet()){
			throw new WechatPayException("total_fee is required");
		}else if(!$inputObj->IsRefund_feeSet()){
			throw new WechatPayException("refund_fee is required.");
		}else if(!$inputObj->IsOp_user_idSet()){
			throw new WechatPayException("op_user_id is required.");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, true, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function refundQuery($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/refundquery";
		//检测必填参数
		if(!$inputObj->IsOut_refund_noSet() &&
			!$inputObj->IsOut_trade_noSet() &&
			!$inputObj->IsTransaction_idSet() &&
			!$inputObj->IsRefund_idSet()) {
			throw new WechatPayException("out_refund_no, out_trade_no, transaction_id and refund_id shoud fill in at least one.");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function downloadBill($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		//检测必填参数
		if(!$inputObj->IsBill_dateSet()) {
			throw new WechatPayException("bill_date is required");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		if(substr($response, 0 , 5) == "<xml>"){
			return "";
		}
		return $response;
	}

	public static function micropay($config, $inputObj, $timeOut = 10)
	{
		$url = "https://api.mch.weixin.qq.com/pay/micropay";
		//检测必填参数
		if(!$inputObj->IsBodySet()) {
			throw new WechatPayException("body is required");
		} else if(!$inputObj->IsOut_trade_noSet()) {
			throw new WechatPayException("out_trade_no is required.");
		} else if(!$inputObj->IsTotal_feeSet()) {
			throw new WechatPayException("total_fee is required.");
		} else if(!$inputObj->IsAuth_codeSet()) {
			throw new WechatPayException("auth_code is required.");
		}

		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function reverse($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";

		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WechatPayException("out_trade_no and transaction_id shoud fill in at least one.");
		}

		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, true, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function report($config, $inputObj, $timeOut = 1)
	{
		$url = "https://api.mch.weixin.qq.com/payitil/report";

		if(!$inputObj->IsInterface_urlSet()) {
			throw new WechatPayException("interface_url is required.");
		} if(!$inputObj->IsReturn_codeSet()) {
			throw new WechatPayException("return_code is required.");
		} if(!$inputObj->IsResult_codeSet()) {
			throw new WechatPayException("result_code is required.");
		} if(!$inputObj->IsUser_ipSet()) {
			throw new WechatPayException("user_ip is required.");
		} if(!$inputObj->IsExecute_time_Set()) {
			throw new WechatPayException("execute_time is required.");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetUser_ip($_SERVER['REMOTE_ADDR']);
		$inputObj->SetTime(date("YmdHis"));
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		return $response;
	}

	public static function bizpayurl($config, $inputObj, $timeOut = 6)
	{
		if(!$inputObj->IsProduct_idSet()){
			throw new WechatPayException("product_id is required when generate QR code");
		}

		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetTime_stamp(time());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);

		return $inputObj->GetValues();
	}


	public static function shorturl($config, $inputObj, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/tools/shorturl";

		if(!$inputObj->IsLong_urlSet()) {
			throw new WechatPayException("long_url is required.");
		}
		$inputObj->SetAppid($config->GetAppId());
		$inputObj->SetMch_id($config->GetMerchantId());
		$inputObj->SetNonce_str(self::getNonceStr());

		$inputObj->SetSign($config);
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();
		$response = self::postXmlCurl($config, $xml, $url, false, $timeOut);
		$result = Results::Init($config, $response);
		self::reportCostTime($config, $url, $startTimeStamp, $result);

		return $result;
	}


	public static function notify($config, $callback, &$msg)
	{
		if (!file_get_contents("php://input")) {
			return false;
		}

		try {
			$xml = file_get_contents("php://input");
			$result = NotifyResults::Init($config, $xml);
		} catch (WechatPayException $e){
			$msg = $e->errorMessage();
			return false;
		}

		return call_user_func($callback, $result);
	}

	public static function getNonceStr($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
		}
		return $str;
	}

	public static function replyNotify($xml)
	{
		return $xml;
	}


	private static function reportCostTime($config, $url, $startTimeStamp, $data)
	{

		$reportLevenl = $config->GetReportLevenl();
		if($reportLevenl == 0){
			return;
		}

		if($reportLevenl == 1 &&
			 array_key_exists("return_code", $data) &&
			 $data["return_code"] == "SUCCESS" &&
			 array_key_exists("result_code", $data) &&
			 $data["result_code"] == "SUCCESS")
		 {
		 	return;
		 }

		$endTimeStamp = self::getMillisecond();
		$objInput = new \Silksoftwarecorp\WechatPay\Model\Api\Data\Report();
		$objInput->SetInterface_url($url);
		$objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);

		if(array_key_exists("return_code", $data)){
			$objInput->SetReturn_code($data["return_code"]);
		}

		if(array_key_exists("return_msg", $data)){
			$objInput->SetReturn_msg($data["return_msg"]);
		}

		if(array_key_exists("result_code", $data)){
			$objInput->SetResult_code($data["result_code"]);
		}

		if(array_key_exists("err_code", $data)){
			$objInput->SetErr_code($data["err_code"]);
		}

		if(array_key_exists("err_code_des", $data)){
			$objInput->SetErr_code_des($data["err_code_des"]);
		}

		if(array_key_exists("out_trade_no", $data)){
			$objInput->SetOut_trade_no($data["out_trade_no"]);
		}

		if(array_key_exists("device_info", $data)){
			$objInput->SetDevice_info($data["device_info"]);
		}

		try{
			self::report($config, $objInput);
		} catch (WechatPayException $e){

		}
	}


	private static function postXmlCurl($config, $xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		$curlVersion = curl_version();
		$ua = "WXPaySDK/3.0.9 (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." "
		.$config->GetMerchantId();


		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
		$config->GetProxy($proxyHost, $proxyPort);

		if($proxyHost != "0.0.0.0" && $proxyPort != 0){
			curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
			curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
		curl_setopt($ch,CURLOPT_USERAGENT, $ua);

		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		if($useCert == true){

			$sslCertPath = "";
			$sslKeyPath = "";
			$config->GetSSLCertPath($sslCertPath, $sslKeyPath);
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, $sslCertPath);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, $sslKeyPath);
		}

		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

		$data = curl_exec($ch);

		if($data){
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			throw new WechatPayException("curl error, error code: $error");
		}
	}


	private static function getMillisecond()
	{
		
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}
