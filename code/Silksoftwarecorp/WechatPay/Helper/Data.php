<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Helper;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Silksoftwarecorp\WechatPay\Model\Method\WechatPay as PaymentMethod;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $methodCode = PaymentMethod::METHOD_CODE;
    /**
     * Store Manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Scope configuration
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    protected $_localeCurrency;
    /**
     *
     * @param \Magento\Framework\App\Helper\Context         $context
     * @param \Magento\Store\Model\StoreManagerInterface    $storeManager
     * @param \Magento\Framework\HTTP\Header                $httpHeader
     * @param \Magento\Directory\Helper\Data                $directoryHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_directoryHelper = $directoryHelper;
        $this->_localeCurrency = $localeCurrency;
    }


    /**
     * Get Store Config Value
     * @param  string $path
     * @return string
     */
    public function getStoreConfigValue($path){
        $store = $this->_storeManager->getStore()->getId();
         return $this->_scopeConfig->getValue(
            $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store
        );
    }

    /**
     * get alipay payment config value
     * @param  string $field
     * @param  string|null $storeId
     * @return string
     */
    public function getPaymentConfig($field, $storeId=null){
        if($storeId == null){
            $storeId = $this->_storeManager->getStore()->getId();
        }

         return $this->_scopeConfig->getValue(
            'payment/'.$this->methodCode.'/'.$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * getUrl
     * @param  string $route
     * @param  array  $params
     * @return string
     */
    public function getUrl($route, $params=[]){
        return $this->_getUrl($route, $params);
    }

    /**
     * Check User Agent is Mobile
     * @return boolean
     */
    public function isMobile(){
      $userAgent = $this->_httpHeader->getHttpUserAgent();
      return \Zend_Http_UserAgent_Mobile::match($userAgent, $_SERVER);
    }

    public function isInWechat(){
        $userAgent = $this->_httpHeader->getHttpUserAgent();
        //var_dump($userAgent);
        if (strpos($userAgent, 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    public function getRequest(){
        return $this->_getRequest();
    }

    public function formatTotal($total, $currencyCode){
        if(in_array($currencyCode, array('JPY', 'KRW'))){
            return intval($total);
        }else{
            return intval(($total * 100));
        }
    }

    public function formatTime($format, $expire = null){
        if($expire){
            $time = time() + intval($expire);
        }else{
            $time = time();
        }
        $date = date('Y-m-d H:i:s', $time);
        $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
        $dateTime->setTimezone(new \DateTimeZone('PRC'));
        return $dateTime->format($format);

    }

    /**
     * Convert currency
     *
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return float
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function currencyConvert($amount, $from, $to = null)
    {
        return $this->_directoryHelper->currencyConvert($amount, $from, $to);
    }


    public function formatPrice($price, $currencyCode, $options=[]){
        return $this->_localeCurrency->getCurrency($currencyCode)->toCurrency($price, $options);
    }

}
