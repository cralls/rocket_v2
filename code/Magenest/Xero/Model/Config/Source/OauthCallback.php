<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 04/12/2019
 * Time: 13:35
 */

namespace Magenest\Xero\Model\Config\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class OauthCallback extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $storeConfig;

    protected $storeManager;

    public function __construct(
        Context $context,
        StoreConfigInterface $storeConfig,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
        parent::__construct($context, $data);
    }

    /**
     * create element for Access token field in store configuration
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $copy = "var copyText = this;copyText.select();document.execCommand('copy');alert('Copied the Redirect Uri: ' + copyText.value);";
        $url = $this->getWebhookUri();
        $element->addData([
            'value' => $url,
            'tooltip' => 'Set this Callback Uri value in your Xero OAuth 2.0 setting',
            'comment' => '<a href="https://developer.xero.com/myapps" target="_blank">Click here to navigate to go to Xero\'s app page</a>',
            'onclick' => $copy,
        ]);

        return parent::_renderValue($element);
    }

    public function getWebhookUri()
    {
        $storeCode = "";
        $useUrlRewrites = $this->_scopeConfig->getValue(
            'web/seo/use_rewrites',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $uriIndex = '';
        if (! $useUrlRewrites) {
            $uriIndex = 'index.php/';
        }

        if ($scopeId = $this->_request->getParam('website')) {
            if ($this->_scopeConfig->getValue('web/url/use_store')) {
                $website = $this->storeManager->getWebsite($scopeId);
                $group = $this->storeManager->getGroup($website->getDefaultGroupId());
                $store = $this->storeManager->getStore($group->getDefaultStoreId());
                $storeCode = $store->getCode()."/";
            }
            return $this->_scopeConfig->getValue('web/secure/base_url', 'website', $scopeId).$uriIndex. $storeCode.'xero/oauth/callback/';
        } else {
            if ($this->_scopeConfig->getValue('web/url/use_store')) {
                $store = $this->storeManager->getDefaultStoreView();
                $storeCode = $store->getCode()."/";
            }
            return $this->_scopeConfig->getValue('web/secure/base_url').$uriIndex. $storeCode.'xero/oauth/callback/';
        }
    }
}
