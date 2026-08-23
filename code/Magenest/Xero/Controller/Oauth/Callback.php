<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 04/12/2019
 * Time: 13:28
 */

namespace Magenest\Xero\Controller\Oauth;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\CoreConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magenest\Xero\Model\XeroClient;
use Magenest\Xero\Model\Synchronization;

class Callback extends Action
{
    protected $config;

    protected $client;

    protected $flagManager;

    protected $item;

    protected $customer;

    public function __construct(
        Context $context,
        CoreConfig $config,
        XeroClient $client,
        Synchronization\Customer $customer,
        Synchronization\Item $item
    ) {
        $this->config = $config;
        $this->client = $client;
        $this->customer = $customer;
        $this->item = $item;
        parent::__construct($context);
    }

    public function execute()
    {
        $state = $this->getRequest()->getParam('state');
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $stateParts = explode("-", $state);

        $result->setContents("Something went wrong while processing your request!<br />
        Please check your credentials, save the Magento configuration and try again!");

        if (count($stateParts) == 3 && is_numeric($stateParts[1])
            && in_array($stateParts[0], [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, ScopeInterface::SCOPE_WEBSITES])) {
            $storedState = $this->config
                ->getConfigValueByScope(Signature::PATH_CLIENT_STATE, $stateParts[0], $stateParts[1]);
            if ($state != $storedState) {
                return $result;
            }
        }

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $this->getRequest()->getParam('code'),
            'redirect_uri' => $this->_url->getUrl('*/*/callback', ['_secure' => true])
        ];
        $code = $this->client->requestNewAccessCode($params, $stateParts[0], $stateParts[1]);
        $this->client->getUserInformation();
        if ($code) {
            $result->setContents("The connection has been initialized successfully!<br />
            You now can close this window and refresh the configuration page!");
        }
        $this->syncAdditionalItem();
        $this->syncTransactionContact();
        return $result;
    }

    protected function syncAdditionalItem()
    {
        $xml = $this->item->getShippingItemXml();
        $xml .= $this->item->getAdjustmentRefundXml();
        $xml .= $this->item->getAmountHandlerXml();
        $xml = '<Items>'.$xml.'</Items>';
        $this->item->syncData($xml);
    }

    protected function syncTransactionContact()
    {
        $xml = $this->customer->getTransactionContactXml();
        $this->customer->syncData($xml);
    }
}
