<?php

namespace Magenest\Xero\Model\Config\Account;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\Parser;
use Magenest\Xero\Model\XeroClient;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config;
use Magento\Framework\App\RequestInterface;
use Magenest\Xero\Model\Helper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Option\ArrayInterface;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;

abstract class XeroAccount extends DataObject implements ArrayInterface, SourceInterface
{
    const CACHE_ID = 'XEROACCOUNTS';

    protected $xeroClient;

    protected static $accounts = null;

    protected $types = [];

    protected $_options = [];

    protected $useCode = false;

    protected $_attribute;

    protected $_cache;

    protected $_request;

    protected $_helper;

    protected $_cacheId;

    protected $_storeManager;

    protected $_serializer;

    public function __construct(
        XeroClient $xeroClient,
        CacheInterface $cache,
        RequestInterface $request,
        Helper $helper,
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->xeroClient = $xeroClient;
        $this->_cache = $cache;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
        $this->_serializer = $serializer;
        $this->setCacheId();
        $this->prepareOptions();
        parent::__construct($data);
    }

    protected function prepareOptions()
    {
        if ($this->_helper->isMultipleWebsiteEnable()) {
            if ($this->_request->getParam('store')) {
                $websiteId = $this->_storeManager->getStore($this->_request->getParam('store'))->getWebsiteId();
                $this->_helper->setScope('websites');
                $this->_helper->setScopeId($websiteId);
            }
        }
        if ($cached = $this->_cache->load($this->_cacheId)) {
            try {
                self::$accounts = $this->_serializer->unserialize($cached);
            } catch (\Exception $exception) {
                self::$accounts = [];
            }
        }
        if (self::$accounts === null && $this->_helper->getConfig(Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED)) {
            try {
                $helper = $this->xeroClient->getSignature();
                $helper->setUri('Accounts');
                $helper->setMethod();
                $url = $helper->getUri();
                $response = $this->xeroClient->sendRequest($url);
                $parsedResponse = $this->_helper->parseXML($response);
                if (isset($parsedResponse['Accounts']['Account'])) {
                    self::$accounts = $parsedResponse['Accounts']['Account'];
                } else {
                    self::$accounts = [];
                }
                $this->_cache->save($this->_serializer
                    ->serialize(self::$accounts), $this->_cacheId, [Config::CACHE_TAG]);
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()
                    ->create(\Psr\Log\LoggerInterface::class)
                    ->debug('Xero get Accounts error. ' . $e->getMessage());
            }
        }
    }

    protected function loadOptions()
    {
        if (!is_array(self::$accounts)) {
            return $this->_options;
        }
        if ($this->isEmpty(self::$accounts)) {
            $this->prepareOptions();
        }
        foreach (self::$accounts as $account) {
            if (isset($account['Type']) && in_array($account['Type'], $this->types)) {
                $value = $this->useCode ? $account['Code'] : $account['AccountID'];
                $name = $account['Name'];
                $code = isset($account['Code']) ? '[' . $account['Code'] . ']' : '';
                $this->_options[$value] = $name . $code;
            }
        }
        return $this->_options;
    }

    public function toOptionArray()
    {
        if (!is_array(self::$accounts)) {
            return $this->_options;
        }
        $this->_options[''] = '-- Please Select --';
        foreach (self::$accounts as $account) {
            if (isset($account['Type']) && in_array($account['Type'], $this->types)) {
                $value = $this->useCode ? $account['Code'] : $account['AccountID'];
                $name = $account['Name'];
                $code = isset($account['Code']) ? '[' . $account['Code'] . ']' : '';
                $this->_options[$value] = $name . $code;
            }
        }
        return $this->_options;
    }

    /*
     * @param $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**----------
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    public function getAllOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $key => $value) {
            $option['label'] = __($value);
            $option['value'] = $key;
            $options[] = $option;
        }
        return $options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    protected function setCacheId()
    {
        if ($this->_helper->isMultipleWebsiteEnable()) {
            if ($this->_request->getParam('store')) {
                $scopeId = $this->_storeManager->getStore($this->_request->getParam('store'))->getWebsiteId();
            } else {
                $scopeId = $this->_request->getParam('website');
            }
            $this->_cacheId = self::CACHE_ID;
            if ($scopeId) {
                $this->_helper->setScopeId($scopeId);
                $this->_helper->setScope(ScopeInterface::SCOPE_WEBSITES);
                $this->_cacheId .= "_" . $scopeId;
            }
        }
    }
}
