<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api\Data;

class NotifyResults extends Results
{

	public static function Init($config, $xml)
	{
		$obj = new self();
		$obj->FromXml($xml);
		$obj->CheckSign($config);
        return $obj;
	}
}
