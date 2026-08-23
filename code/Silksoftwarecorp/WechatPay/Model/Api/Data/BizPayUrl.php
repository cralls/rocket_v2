<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
class BizPayUrl extends DataBase
{
	
	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}

	public function GetAppid()
	{
		return $this->values['appid'];
	}

	public function IsAppidSet()
	{
		return array_key_exists('appid', $this->values);
	}



	public function SetMch_id($value)
	{
		$this->values['mch_id'] = $value;
	}

	public function GetMch_id()
	{
		return $this->values['mch_id'];
	}

	public function IsMch_idSet()
	{
		return array_key_exists('mch_id', $this->values);
	}


	public function SetTime_stamp($value)
	{
		$this->values['time_stamp'] = $value;
	}

	public function GetTime_stamp()
	{
		return $this->values['time_stamp'];
	}

	public function IsTime_stampSet()
	{
		return array_key_exists('time_stamp', $this->values);
	}


	public function SetNonce_str($value)
	{
		$this->values['nonce_str'] = $value;
	}

	public function GetNonce_str()
	{
		return $this->values['nonce_str'];
	}

	public function IsNonce_strSet()
	{
		return array_key_exists('nonce_str', $this->values);
	}


	public function SetProduct_id($value)
	{
		$this->values['product_id'] = $value;
	}


	public function GetProduct_id()
	{
		return $this->values['product_id'];
	}


	public function IsProduct_idSet()
	{
		return array_key_exists('product_id', $this->values);
	}

}
