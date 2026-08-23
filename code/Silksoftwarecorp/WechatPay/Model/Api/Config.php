<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Api;
use Silksoftwarecorp\WechatPay\Model\Method\WechatPay as PaymentMethod;
class Config extends \Magento\Framework\DataObject
{

    private $appId;
    private $merchantId;
    private $key;
    private $appSecret;

    protected $helper;

    /**
     *
     * @param \Silksoftwarecorp\WechatPay\Helper\Data $helper
     * @param array                               $data
     */
    public function __construct(
        \Silksoftwarecorp\WechatPay\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($data);
    }

	public function GetAppId()
	{
        if(!$this->appId){
            $this->appId = $this->helper->getPaymentConfig('app_id');
        }
		return $this->appId;
	}

	public function GetMerchantId()
	{
        if(!$this->merchantId){
            $this->merchantId = $this->helper->getPaymentConfig('merchant_id');
        }
		return $this->merchantId;
	}


	public function GetNotifyUrl()
	{
		return $this->helper->getUrl(PaymentMethod::NOTIFY_URL_PATH);
	}

	public function GetSignType()
	{
    return $this->helper->getPaymentConfig('sign_type');
		//return "HMAC-SHA256";
	}

	public function GetProxy(&$proxyHost, &$proxyPort)
	{
		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
	}


	public function GetReportLevenl()
	{
		return 1;
	}


	public function GetKey()
	{
        if(!$this->key){
            $this->key = $this->helper->getPaymentConfig('key');
        }
		return $this->key;
	}

	public function GetAppSecret()
	{
        if(!$this->appSecret){
            $this->appSecret = $this->helper->getPaymentConfig('app_secret');
        }
		return $this->appSecret;
	}


	public function GetSSLCertPath(&$sslCertPath, &$sslKeyPath)
	{
        $sslCertPath = $this->helper->getPaymentConfig('apiclient_cert_path');
        $sslKeyPath  = $this->helper->getPaymentConfig('apiclient_key_path');
	}
}
