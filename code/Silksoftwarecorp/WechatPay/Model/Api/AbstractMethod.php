<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api;
use Silksoftwarecorp\WechatPay\Model\Api\Data\BizPayUrlFactory;

abstract class AbstractMethod extends \Magento\Framework\DataObject
{
    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Api\Config
     */
    protected $config;

    /**
     * @var \Silksoftwarecorp\WechatPay\Helper\Data
     */
    protected $helper;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Api\Config
     */
    protected $logger;

    /**
     * @param \Silksoftwarecorp\WechatPay\Helper\Data       $helper
     * @param \Silksoftwarecorp\WechatPay\Model\Logger      $logger
     * @param \Silksoftwarecorp\WechatPay\Model\Api\Config  $config
     * @param array                                         $data
     */
    public function __construct(
        \Silksoftwarecorp\WechatPay\Helper\Data $helper,
        \Silksoftwarecorp\WechatPay\Model\Logger $logger,
        \Silksoftwarecorp\WechatPay\Model\Api\Config $config,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->config = $config;
        parent::__construct($data);
    }

    protected function getConfig(){
        return $this->config;
    }

    protected function getRequest(){
	return $this->helper->getRequest();
    }

    
	protected function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			$buff .= $k . "=" . $v . "&";
		}

		$buff = trim($buff, "&");
		return $buff;
	}

}
