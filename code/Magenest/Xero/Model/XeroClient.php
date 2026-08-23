<?php
/**
 * Copyright © 2015 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Xero extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_Xero
 * @author   ThaoPV
 */
namespace Magenest\Xero\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ZendClient;
use Magenest\Xero\Helper\Signature;

class XeroClient extends ZendClient
{
    /**
     * @var Signature
     */
    protected $signature;

    /**
     * @var array
     */
    private $params;

    protected $scopeConfig;

    protected $configModel;

    protected $cache;

    protected $helper;

    protected $xeroCache;

    /**
     * XeroClient constructor.
     * @param Signature $signature
     * @param ScopeConfigInterface $scopeConfig
     * @param CacheInterface $cache
     * @param Helper $helper
     * @param Cache $xeroCache
     * @param null $uri
     * @param null $config
     */
    public function __construct(
        Signature $signature,
        ScopeConfigInterface $scopeConfig,
        CacheInterface $cache,
        Helper $helper,
        Cache $xeroCache,
        $uri = null,
        $config = null
    ) {
        $config['timeout'] = 30;
        parent::__construct($uri, $config);
        $this->signature = $signature;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->helper = $helper;
        $this->xeroCache = $xeroCache;
        $this->config['useragent'] = $this->getUserAgent();
    }

    public function getUserAgent()
    {
        return 'MagenestMagento2Connector_' . ObjectManager::getInstance()->get(ScopeConfigInterface::class)
                ->getValue(\Magenest\Xero\Model\Config::XML_PATH_XERO_TENANT_ID);
    }

    /**
     * @return Signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Get Params
     *
     * @return array
     */
    private function getParams()
    {
        if ($this->params === null) {
            $this->params = [];
        }

        return $this->params;
    }

    /**
     * @param $params
     * @return $this
     */
    private function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @param bool $addCode
     * @return string
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Http_Exception
     */
    public function sendRequest($url, $params = [], $method = "GET", $addCode = true)
    {
        if ($addCode) {
            $code = $this->getAccessCode();
            $this->setHeaders('Authorization', 'Bearer '.$code);
        }
        if (strpos($url, Signature::URL_XERO_API) === 0) {
            $this->setHeaders('xero-tenant-id', $this->helper->getConfig(Signature::PATH_XERO_TENANT_ID));
        }
        $this->setHeaders('Content-type', 'application/x-www-form-urlencoded;charset:UTF-8');
        $this->setUri($url);
        if ($method == "POST" || $method == "PUT") {
            $this->setParameterPost($params);
        } elseif ($method == "GET") {
            $this->setParameterGet($params);
        }
        $response = $this->request($method)->getBody();
        $this->resetParameters();
        $status = $this->getLastResponse()->getStatus();
        if ($status >= 400) {
            $this->processResponse($response);
            throw new \Zend_Http_Exception(urldecode($response), $status);
        }
        return $response;
    }

    /**
     * @return $this|ZendClient
     * @throws \Zend_Http_Client_Adapter_Exception
     * @throws \Zend_Http_Client_Exception
     */
    protected function _trySetCurlAdapter()
    {
        if (extension_loaded('curl')) {
            $this->setAdapter(new \Zend_Http_Client_Adapter_Curl());
        }

        return $this;
    }

    /**
     * Get User Information
     *
     * @return array|bool
     * @throws \Exception
     */
    // phpcs:disable Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
    public function getUserInformation()
    {
        $path = 'organisation';
        $signatureHelper = $this->getSignature();
        $signatureHelper->setUri($path);
        $url = $this->scopeConfig->getValue(Signature::URL_XERO_OAUTH_CONNECTIONS);

        $response = $this->sendRequest($url, [], 'GET');
        $result = json_decode($response, true) ? : [];
        foreach ($result as $org) {
            $this->helper->saveConfig(Signature::PATH_CONNECTED_ID, $org['id']);
            $this->helper->saveConfig(Signature::PATH_XERO_TENANT_ID, $org['tenantId']);
            $response = $this->sendRequest($signatureHelper->getUri());
            $result = array_merge($this->helper->parseXML($response), $result);
            $result = $result['Organisations']['Organisation'];
            $result = [
                'Organisation' => $result['Name'],
                'Organisation Status' => $result['OrganisationStatus'],
                'Country' => $result['CountryCode'],
                'Version' => $result['Version']
            ];
        }

        return $result;
    }

    protected function processResponse($response)
    {
        $arrResult = json_decode($response);
        if ($arrResult && is_array($arrResult)) {
            if (isset($arrResult['oauth_problem']) && $arrResult['oauth_problem'] == 'token_expired') {
                $this->disconnectApp();
            }
        }
    }

    public function disconnectApp()
    {
        $url = $this->scopeConfig->getValue(Signature::URL_XERO_OAUTH_DELETE);
        $url .= $this->helper->getConfig(Signature::PATH_CONNECTED_ID);
        try {
            $this->sendRequest($url, [], "DELETE");
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)
                ->debug('Xero Exception: '.$e->getMessage());
        }
        $this->helper->saveConfig(Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED, 0);
        $this->helper->saveConfig(Signature::PATH_REFRESH_TOKEN, null);

        $this->xeroCache->refreshCache();
    }

    protected function getAccessCode()
    {
        $code = $this->cache->load($this->helper->generateCacheIdByScope('access_token'));
        if (!$code) {
            $params = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->helper->getRefreshCode(),
            ];
            $code = $this->requestNewAccessCode($params, $this->helper->getScope(), $this->helper->getScopeId());
        }
        return $code;
    }

    public function requestNewAccessCode($params, $scope, $id)
    {
        $this->helper->setScope($scope);
        $this->helper->setScopeId($id);
        $this->setHeaders('Authorization', 'Basic '.$this->helper->getAuthorization());
        $response = $this->sendRequest(
            $this->scopeConfig->getValue(Signature::URL_XERO_OAUTH_TOKEN, $scope, $id),
            $params,
            'POST',
            false
        );
        $response = json_decode($response, true);
        if (isset($response['access_token'])) {
            $this->cache->save(
                $response['access_token'],
                $this->helper->generateCacheIdByScope('access_token'),
                [],
                $response['expires_in'] - 60
            );
            $this->helper->saveConfig(Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED, 1);
            $this->helper->saveConfig(Signature::PATH_REFRESH_TOKEN, $response['refresh_token']);
            return $response['access_token'];
        } else {
            return false;
        }
    }
}
