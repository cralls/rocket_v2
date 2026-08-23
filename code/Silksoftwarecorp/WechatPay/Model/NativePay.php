<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silksoftware (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model;

use Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi;

class NativePay extends \Silksoftwarecorp\WechatPay\Model\Api\AbstractMethod
{


	public function GetPrePayUrl($productId)
	{
		$biz = $this->bizPayUrlFactory->create();
		$biz->SetProduct_id($productId);
		try{
			$config = $this->getConfig();
			$values = WechatPayApi::bizpayurl($config, $biz);
		} catch(\Exception $e) {
            $this->logger->error($e->getMessage());
		}
		$url = "weixin://wxpay/bizpayurl?" . $this->ToUrlParams($values);
		return $url;
	}

	
	public function GetPayUrl($input)
	{
		if($input->GetTrade_type() == "NATIVE")
		{
			try{
				$config = $this->getConfig();
				$result = WechatPayApi::unifiedOrder($config, $input);
				return $result;
			} catch(\Exception $e) {
				$this->logger->error($e->getMessage());
			}
		}
		return false;
	}
}
