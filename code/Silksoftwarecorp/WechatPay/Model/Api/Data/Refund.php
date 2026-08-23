<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;

class Refund extends DataBase
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

	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}

	public function GetDevice_info()
	{
		return $this->values['device_info'];
	}

	public function IsDevice_infoSet()
	{
		return array_key_exists('device_info', $this->values);
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


	public function SetOut_refund_no($value)
	{
		$this->values['out_refund_no'] = $value;
	}

	public function GetOut_refund_no()
	{
		return $this->values['out_refund_no'];
	}

	public function IsOut_refund_noSet()
	{
		return array_key_exists('out_refund_no', $this->values);
	}


	public function SetTotal_fee($value)
	{
		$this->values['total_fee'] = $value;
	}

	public function GetTotal_fee()
	{
		return $this->values['total_fee'];
	}

	public function IsTotal_feeSet()
	{
		return array_key_exists('total_fee', $this->values);
	}

	public function SetRefund_fee($value)
	{
		$this->values['refund_fee'] = $value;
	}

	public function GetRefund_fee()
	{
		return $this->values['refund_fee'];
	}

	public function IsRefund_feeSet()
	{
		return array_key_exists('refund_fee', $this->values);
	}


	public function SetRefund_fee_type($value)
	{
		$this->values['refund_fee_type'] = $value;
	}

	public function GetRefund_fee_type()
	{
		return $this->values['refund_fee_type'];
	}

	public function IsRefund_fee_typeSet()
	{
		return array_key_exists('refund_fee_type', $this->values);
	}

	public function SetOp_user_id($value)
	{
		$this->values['op_user_id'] = $value;
	}

	public function GetOp_user_id()
	{
		return $this->values['op_user_id'];
	}
	
	public function IsOp_user_idSet()
	{
		return array_key_exists('op_user_id', $this->values);
	}
}
