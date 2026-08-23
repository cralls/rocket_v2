<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi;
use Silksoftwarecorp\WechatPay\Model\Api\Data\OrderQuery;

class Notify extends  DataBase
{

	public function SetReturn_code($return_code)
	{
		$this->values['return_code'] = $return_code;
	}


	public function GetReturn_code()
	{
		return $this->values['return_code'];
	}


	public function SetReturn_msg($return_msg)
	{
		$this->values['return_msg'] = $return_msg;
	}


	public function GetReturn_msg()
	{
		return $this->values['return_msg'];
	}


	public function SetData($key, $value)
	{
		$this->values[$key] = $value;
	}

    private $config = null;

	final public function Handle($config, $needSign = true)
	{
		$this->config = $config;
		$msg = "OK";

		$result = WechatPayApi::notify($config, array($this, 'NotifyCallBack'), $msg);
		if($result == false){
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
			return $this->ReplyNotify(false);
		} else {
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		}
		return $this->ReplyNotify($needSign);
	}

	public function Queryorder($transaction_id)
	{
		$input = new OrderQuery();
		$input->SetTransaction_id($transaction_id);

		$result = WechatPayApi::orderQuery($this->config, $input);
		$this->logger->debug("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}


	public function LogAfterProcess($xmlData)
	{
		$this->logger->debug("call back， return xml:" . $xmlData);
		return;
	}


	public function NotifyProcess($objData, $config, &$msg)
	{
		$data = $objData->GetValues();
		if(!array_key_exists("return_code", $data)
			||(array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
			$msg = "FAIL";
			return false;
		}
		if(!array_key_exists("transaction_id", $data)){
			$msg = "Transcation ID does not exist";
			return false;
		}

		try {
			$checkResult = $objData->CheckSign($config);
			if($checkResult == false){
				$this->logger->error("Sing Error");
				return false;
			}
		} catch(\Exception $e) {
			$this->logger->error(json_encode($e));
		}

		$this->logger->debug("call back:" . json_encode($data));
		$notfiyOutput = array();


		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "Order query failed";
			return false;
		}
		return true;
	}



	final public function NotifyCallBack($data)
	{
		$msg = "OK";
		$result = $this->NotifyProcess($data, $this->config, $msg);

		if($result == true){
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		} else {
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
		}
		return $result;
	}


	final public function ReplyNotify($needSign = true)
	{
		if($needSign == true &&
			$this->GetReturn_code() == "SUCCESS")
		{
			$this->SetSign($this->config);
		}

		$xml = $this->ToXml();
		$this->LogAfterProcess($xml);
		return WechatPayApi::replyNotify($xml);
	}
}
