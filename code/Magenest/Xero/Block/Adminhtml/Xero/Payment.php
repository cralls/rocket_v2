<?php
namespace Magenest\Xero\Block\Adminhtml\Xero;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\Helper;
use Magenest\Xero\Model\PaymentMappingFactory;
use Magenest\Xero\Model\XeroClient;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;

class Payment extends Widget
{

    protected $xeroClient;

    protected $acceptType = ['BANK'];

    protected $mappingFactory;

    protected $_cache;

    protected $_helper;

    protected $_serializer;

    /**
     * Payment constructor.
     * @param Context $context
     * @param XeroClient $xeroClient
     * @param PaymentMappingFactory $mappingFactory
     * @param Helper $helper
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        XeroClient $xeroClient,
        PaymentMappingFactory $mappingFactory,
        Helper $helper,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->xeroClient = $xeroClient;
        $this->mappingFactory = $mappingFactory;
        $this->_helper = $helper;
        $this->_serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getPaymentMethods()
    {
        $methods = $this->_scopeConfig->getValue('payment');
        foreach ($methods as $code => $method) {
            if (isset($method['active']) && $method['active'] == 0) {
                unset($methods[$code]);
            }
        }
        return $methods;
    }

    public function getWebsiteId()
    {
        return $this->getRequest()->getParam('website') ? : 0;
    }

    /**
     * Retrieve Xero account from Cache or load from API
     *
     * @return array|bool|float|int|string|null
     */
    public function getAccounts()
    {
        $websiteId = $this->getWebsiteId();
        if (!$this->_helper->isMultipleWebsiteEnable()) {
            $websiteId = 0;
        }
        if ($websiteId > 0) {
            $this->_helper->setScope('websites');
            $this->_helper->setScopeId($websiteId);
        }
        $cacheData = $this->_cache->load('XERO_BANK_ACCOUNTS_'.$websiteId);
        try {
            $accounts = $this->_serializer->unserialize($cacheData);
        } catch (\Exception $exception) {
            $accounts = [];
        }
        if (!$accounts) {
            if (!$this->_scopeConfig->getValue(Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED)) {
                return '(Your Xero has been disconnected! Please connect to
                your Xero before configuring this mapping!)';
            }
            try {
                $helper = $this->xeroClient->getSignature();
                $helper->setUri('Accounts');
                $url = $helper->getUri();
                $response = $this->xeroClient->sendRequest(
                    $url,
                    ['where' => 'EnablePaymentsToAccount = true OR TYPE = "BANK"']
                );

                if (strpos($response, 'oauth_problem') !== false) {
                    return '(Invalid Token! Please check your Xero credential before configuring this mapping!)';
                }
                $parser = new \Magento\Framework\Xml\Parser();
                $parser->loadXML($response);
                $parsedResponse = $parser->xmlToArray();
                if (isset($parsedResponse['Response']['Accounts']['Account'])) {
                    $accounts = $parsedResponse['Response']['Accounts']['Account'];
                    $cacheData = $this->_serializer->serialize($accounts);
                    $this->_cache->save($cacheData, 'XERO_BANK_ACCOUNTS_'.$websiteId, ['config']);
                    return $accounts;
                }
                return 'Can not get Xero Accounts. Response: '.$response;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        return $accounts;
    }

    public function getSelectedMapping($paymentCode)
    {
        $mapping = $this->mappingFactory->create()->loadByPaymentCode($paymentCode);
        if ($mapping) {
            return $mapping->getBankAccountId();
        }
        return null;
    }
}
