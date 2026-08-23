<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Helper;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
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
        \Magento\Directory\Helper\Data $directoryHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_directoryHelper = $directoryHelper;
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
            'payment/alipay/'.$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
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
        try{
            return $this->_directoryHelper->currencyConvert($amount, $from, $to);
        }catch(\Exception $e){
            return $amount;
        }

    }

}
