<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
class Report extends DataBase
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

	public function SetInterface_url($value)
	{
		$this->values['interface_url'] = $value;
	}

	public function GetInterface_url()
	{
		return $this->values['interface_url'];
	}

	public function IsInterface_urlSet()
	{
		return array_key_exists('interface_url', $this->values);
	}



	public function SetExecute_time_($value)
	{
		$this->values['execute_time_'] = $value;
	}

	public function GetExecute_time_()
	{
		return $this->values['execute_time_'];
	}

	public function IsExecute_time_Set()
	{
		return array_key_exists('execute_time_', $this->values);
	}



	public function SetReturn_code($value)
	{
		$this->values['return_code'] = $value;
	}

	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}

	public function IsReturn_codeSet()
	{
		return array_key_exists('return_code', $this->values);
	}

	public function SetReturn_msg($value)
	{
		$this->values['return_msg'] = $value;
	}

	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}

	public function IsReturn_msgSet()
	{
		return array_key_exists('return_msg', $this->values);
	}



	public function SetResult_code($value)
	{
		$this->values['result_code'] = $value;
	}

	public function GetResult_code()
	{
		return $this->values['result_code'];
	}

	public function IsResult_codeSet()
	{
		return array_key_exists('result_code', $this->values);
	}


	public function SetErr_code($value)
	{
		$this->values['err_code'] = $value;
	}

	public function GetErr_code()
	{
		return $this->values['err_code'];
	}

	public function IsErr_codeSet()
	{
		return array_key_exists('err_code', $this->values);
	}


	public function SetErr_code_des($value)
	{
		$this->values['err_code_des'] = $value;
	}

	public function GetErr_code_des()
	{
		return $this->values['err_code_des'];
	}

	public function IsErr_code_desSet()
	{
		return array_key_exists('err_code_des', $this->values);
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


	public function SetUser_ip($value)
	{
		$this->values['user_ip'] = $value;
	}

	public function GetUser_ip()
	{
		return $this->values['user_ip'];
	}

	public function IsUser_ipSet()
	{
		return array_key_exists('user_ip', $this->values);
	}

	public function SetTime($value)
	{
		$this->values['time'] = $value;
	}


	public function GetTime()
	{
		return $this->values['time'];
	}
	
	public function IsTimeSet()
	{
		return array_key_exists('time', $this->values);
	}
}
