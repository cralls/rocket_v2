<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;


class JsApiPay extends DataBase
{


	public function SetAppid($value)
	{
		$this->values['appId'] = $value;
	}


	public function GetAppid()
	{
		return $this->values['appId'];
	}


	public function IsAppidSet()
	{
		return array_key_exists('appId', $this->values);
	}




	public function SetTimeStamp($value)
	{
		$this->values['timeStamp'] = $value;
	}


	public function GetTimeStamp()
	{
		return $this->values['timeStamp'];
	}


	public function IsTimeStampSet()
	{
		return array_key_exists('timeStamp', $this->values);
	}



	public function SetNonceStr($value)
	{
		$this->values['nonceStr'] = $value;
	}


	public function GetReturn_code()
	{
		return $this->values['nonceStr'];
	}


	public function IsReturn_codeSet()
	{
		return array_key_exists('nonceStr', $this->values);
	}

	public function SetPackage($value)
	{
		$this->values['package'] = $value;
	}

	public function GetPackage()
	{
		return $this->values['package'];
	}

	public function IsPackageSet()
	{
		return array_key_exists('package', $this->values);
	}

	public function SetSignType($value)
	{
		$this->values['signType'] = $value;
	}

	public function GetSignType()
	{
		return $this->values['signType'];
	}

	public function IsSignTypeSet()
	{
		return array_key_exists('signType', $this->values);
	}


	public function SetPaySign($value)
	{
		$this->values['paySign'] = $value;
	}

	public function GetPaySign()
	{
		return $this->values['paySign'];
	}

	public function IsPaySignSet()
	{
		return array_key_exists('paySign', $this->values);
	}
}
