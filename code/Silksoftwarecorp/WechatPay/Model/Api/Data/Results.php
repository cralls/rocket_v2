<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayException;
class Results extends DataBase
{

	public function MakeSign($config, $needSignType = false)
	{
		ksort($this->values);

		$string = $this->ToUrlParams();

		$string = $string . "&key=".$config->GetKey();

		if(strlen($this->GetSign()) <= 32){
			$string = md5($string);
		} else {
			$string = hash_hmac("sha256",$string ,$config->GetKey());
		}

		$result = strtoupper($string);
		return $result;
	}


	public function CheckSign($config)
	{
		if(!$this->IsSignSet()){
			throw new WechatPayException("Sign String error.");
		}

		$sign = $this->MakeSign($config, false);
		if($this->GetSign() == $sign){
			return true;
		}
		throw new WechatPayException("Sign String error.");
	}


	public function FromArray($array)
	{
		$this->values = $array;
	}


	public static function InitFromArray($config, $array, $noCheckSign = false)
	{
		$obj = new self();
		$obj->FromArray($array);
		if($noCheckSign == false){
			$obj->CheckSign($config);
		}
        return $obj;
	}


	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}


	public static function Init($config, $xml)
	{
		$obj = new self();
		$obj->FromXml($xml);
		//失败则直接返回失败
		if($obj->values['return_code'] != 'SUCCESS') {
			foreach ($obj->values as $key => $value) {
				if($key != "return_code" && $key != "return_msg"){
					throw new WechatPayException("data error");
					return false;
				}
			}
			return $obj->GetValues();
		}
		$obj->CheckSign($config);
        return $obj->GetValues();
	}
}
