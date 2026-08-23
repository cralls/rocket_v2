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
 * @author   ThaoPV <thaopw@gmail.com>
 */
namespace Magenest\Xero\Helper;

use Magenest\Xero\Model\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magenest\Xero\Model\CoreConfig;

class Signature
{
    protected $_version = null;

    /**
     * @var string
     */
    protected $signatureMethod;

    /**
     * @var string
     */
    protected $signature = null;

    /**
     * @var string
     */
    protected $uri;

    /**
     * Method using when sendRequest to Xero
     *
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $headers;

    /**
     * @var string
     */
    protected $consumerKey = null;

    /**
     * @var string
     */
    protected $consumerSecret = null;

    protected $appType = null;

    protected $oauthSecret = null;

    protected $oauthToken = null;

    protected $_coreConfig;

    protected $_helper;

    /**
     * CONSTANT
     */
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const URL_XERO_API = 'https://api.xero.com/api.xro/2.0/';
    const URL_XERO_OAUTH_AUTHORIZE = 'magenest_xero_config/xero_oauth_20/authorize_url';
    const URL_XERO_OAUTH_TOKEN = 'magenest_xero_config/xero_oauth_20/token_url';
    const URL_XERO_OAUTH_CONNECTIONS = 'magenest_xero_config/xero_oauth_20/connections_url';
    const URL_XERO_OAUTH_DELETE = 'magenest_xero_config/xero_oauth_20/delete_url';
    const PATH_REFRESH_TOKEN = 'magenest_xero_config/xero_oauth_20/xero_refresh_token';
    const PATH_CLIENT_STATE = 'magenest_xero_config/xero_oauth_20/state';
    const PATH_CLIENT_ID = 'magenest_xero_config/xero_oauth_20/client_id';
    const PATH_CLIENT_SECRET = 'magenest_xero_config/xero_oauth_20/client_secret';
    const PATH_CONNECTED_ID = 'magenest_xero_config/xero_oauth_20/xero_connected_id';
    const PATH_XERO_TENANT_ID  = 'magenest_xero_config/xero_oauth_20/xero_tenant_id';
    const PATH_XERO_OAUTH_V2_IS_CONNECTED = 'magenest_xero_config/xero_oauth_20/is_connected';
    const REQUEST_TOKEN_PATH = 'RequestToken';
    const AUTHORIZE_PATH = 'Authorize';
    const ACCESS_TOKEN_PATH = 'AccessToken';
    const URL_OAUTH = 'https://api.xero.com/oauth/';

    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * Signature constructor.
     * @param ScopeConfigInterface $config
     * @param CoreConfig $coreConfig
     * @param Helper $helper
     */
    public function __construct(
        ScopeConfigInterface $config,
        CoreConfig $coreConfig,
        Helper $helper
    ) {
        $this->_coreConfig = $coreConfig;
        $this->_helper = $helper;
        $this->_config = $config;
    }

    public function sign()
    {
        return $this->_encodeUrl(true);
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method = self::METHOD_GET)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if (!$this->method) {
            $this->method = self::METHOD_GET;
        }
        return $this->method;
    }

    public function setParamsOAuth($params)
    {
        $this->params = $params;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setUri($path)
    {
        switch ($path) {
            case self::REQUEST_TOKEN_PATH:
            case self::ACCESS_TOKEN_PATH:
            case self::AUTHORIZE_PATH:
                $this->uri = self::URL_OAUTH . $path;
                break;
            default:
                $this->uri = self::URL_XERO_API . $path.'?SummarizeErrors=false';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return $this
     */
    protected function getTimestamp()
    {
        return time();
    }

    protected function _encodeUrl($filter = false)
    {
        $normalized = [];

        ksort($this->params);

        foreach ($this->params as $key => $value) {
            if ($key == 'xml') {
                if ($filter == true) {
                    continue;
                }
            }
            if (is_array($value)) {
                $sort = $value;
                sort($sort);
                foreach ($sort as $subkey => $subvalue) {
                    $normalized[] = $this->_escape($key) . '=' . $this->_escape($subvalue);
                }
            } else {
                $normalized[] = $this->_escape($key) . '=' . $this->_escape($value);
            }
        }

        return implode('&', $normalized);
    }

    /**
     * @param $string
     * @return mixed
     */
    protected function _escape($string)
    {
        return $string;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function escape($str)
    {
        return $this->_escape($str);
    }

    /**
     * @param $xmlPath
     * @return mixed
     */
    protected function _getStoreConfig($xmlPath)
    {
        return $this->_coreConfig->getConfigValueByScope(
            $xmlPath,
            $this->_helper->getScope(),
            $this->_helper->getScopeId()
        );
    }
}
