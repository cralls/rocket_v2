<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
class UnifiedOrder extends DataBase
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


	public function SetBody($value)
	{
		$this->values['body'] = $value;
	}

	public function GetBody()
	{
		return $this->values['body'];
	}

	public function IsBodySet()
	{
		return array_key_exists('body', $this->values);
	}



	public function SetDetail($value)
	{
		$this->values['detail'] = $value;
	}

	public function GetDetail()
	{
		return $this->values['detail'];
	}

	public function IsDetailSet()
	{
		return array_key_exists('detail', $this->values);
	}


	public function SetAttach($value)
	{
		$this->values['attach'] = $value;
	}

	public function GetAttach()
	{
		return $this->values['attach'];
	}

	public function IsAttachSet()
	{
		return array_key_exists('attach', $this->values);
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

	public function SetFee_type($value)
	{
		$this->values['fee_type'] = $value;
	}

	public function GetFee_type()
	{
		return $this->values['fee_type'];
	}

	public function IsFee_typeSet()
	{
		return array_key_exists('fee_type', $this->values);
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

	public function SetSpbill_create_ip($value)
	{
		$this->values['spbill_create_ip'] = $value;
	}

	public function GetSpbill_create_ip()
	{
		return $this->values['spbill_create_ip'];
	}

	public function IsSpbill_create_ipSet()
	{
		return array_key_exists('spbill_create_ip', $this->values);
	}

	public function SetTime_start($value)
	{
		$this->values['time_start'] = $value;
	}

	public function GetTime_start()
	{
		return $this->values['time_start'];
	}

	public function IsTime_startSet()
	{
		return array_key_exists('time_start', $this->values);
	}


	public function SetTime_expire($value)
	{
		$this->values['time_expire'] = $value;
	}

	public function GetTime_expire()
	{
		return $this->values['time_expire'];
	}

	public function IsTime_expireSet()
	{
		return array_key_exists('time_expire', $this->values);
	}

	public function SetGoods_tag($value)
	{
		$this->values['goods_tag'] = $value;
	}

	public function GetGoods_tag()
	{
		return $this->values['goods_tag'];
	}

	public function IsGoods_tagSet()
	{
		return array_key_exists('goods_tag', $this->values);
	}

	public function SetNotify_url($value)
	{
		$this->values['notify_url'] = $value;
	}

	public function GetNotify_url()
	{
		return $this->values['notify_url'];
	}

	public function IsNotify_urlSet()
	{
		return array_key_exists('notify_url', $this->values);
	}

	public function SetTrade_type($value)
	{
		$this->values['trade_type'] = $value;
	}

	public function GetTrade_type()
	{
		return $this->values['trade_type'];
	}

	public function IsTrade_typeSet()
	{
		return array_key_exists('trade_type', $this->values);
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

	public function SetOpenid($value)
	{
		$this->values['openid'] = $value;
	}

	public function GetOpenid()
	{
		return $this->values['openid'];
	}
	
	public function IsOpenidSet()
	{
		return array_key_exists('openid', $this->values);
	}

	public function SetSceneInfo($value){
		$this->values['scene_info'] = $value;
	}

	public function GetSceneInfo(){
		return $this->values['scene_info'];
	}
}
