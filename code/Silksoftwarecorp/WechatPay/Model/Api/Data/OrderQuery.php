<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
class OrderQuery extends DataBase
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

	public function SetTransaction_id($value)
	{
		$this->values['transaction_id'] = $value;
	}

	public function GetTransaction_id()
	{
		return $this->values['transaction_id'];
	}

	public function IsTransaction_idSet()
	{
		return array_key_exists('transaction_id', $this->values);
	}

	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}

	public function GetOut_trade_no()
	{
		return $this->values['out_trade_no'];
	}

	public function IsOut_trade_noSet()
	{
		return array_key_exists('out_trade_no', $this->values);
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
}
