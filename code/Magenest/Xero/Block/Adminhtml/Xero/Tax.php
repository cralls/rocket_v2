<?php
namespace Magenest\Xero\Block\Adminhtml\Xero;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\Helper;
use Magenest\Xero\Model\TaxMappingFactory;
use Magenest\Xero\Model\XeroClient;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;

class Tax extends Widget
{

    protected $xeroClient;

    protected $taxModelConfig;

    protected $mappingFactory;

    protected $_cache;

    protected $_helper;

    protected $_serializer;

    /**
     * Tax constructor.
     * @param Context $context
     * @param XeroClient $xeroClient
     * @param TaxMappingFactory $mappingFactory
     * @param Rate $taxModelConfig
     * @param Helper $helper
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        XeroClient $xeroClient,
        TaxMappingFactory $mappingFactory,
        Rate $taxModelConfig,
        Helper $helper,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->xeroClient = $xeroClient;
        $this->mappingFactory = $mappingFactory;
        $this->taxModelConfig = $taxModelConfig;
        $this->_helper = $helper;
        $this->_serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getTaxRates()
    {
        $taxRates = $this->taxModelConfig->getCollection()->getData();
        $methods = [];
        foreach ($taxRates as $tax) {
            $methods[$tax['code']] = $tax;
        }
        return $methods;
    }

    public function getWebsiteId()
    {
        return $this->_request->getParam('website') ? : 0;
    }

    /**
     * Retrieve tax rate from cache or load from API
     *
     * @return array|bool|float|int|string|null
     */
    public function getTaxes()
    {
         $websiteId = $this->_request->getParam('website') ? : 0;
        if ($websiteId) {
            $this->_helper->setScope('websites');
            $this->_helper->setScopeId($websiteId);
        }
        $cacheData = $this->_cache->load('XERO_TAX_RATES_'.$websiteId);
        try {
            $accounts = $this->_serializer->unserialize($cacheData);
        } catch (\Exception $e) {
            $accounts = [];
        }
        if (!$accounts) {
            if (!$this->_helper->getConfig(Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED)) {
                return '(Your Xero has been disconnected! 
                Please connect to your Xero before configuring this mapping!)';
            }
            try {
                $helper = $this->xeroClient->getSignature();
                $helper->setUri('Taxrates');
                $url = $helper->getUri();
                $response = $this->xeroClient->sendRequest($url);

                if (strpos($response, 'oauth_problem') !== false) {
                    return '(Invalid Token! Please check your Xero credential before configuring this mapping!)';
                }
                $parser = new \Magento\Framework\Xml\Parser();
                $parser->loadXML($response);
                $parsedResponse = $parser->xmlToArray();
                if (isset($parsedResponse['Response']['TaxRates']['TaxRate'])) {
                    $taxes = $parsedResponse['Response']['TaxRates']['TaxRate'];
                    $cacheData = $this->_serializer->serialize($taxes);
                    $this->_cache->save($cacheData, 'XERO_TAX_RATES_'.$websiteId, ['config']);
                    return $taxes;
                }
                return 'Can not get Xero Acconts. Response: '.$response;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        return $accounts;
    }

    public function getSelectedMapping($taxCode)
    {
        $mapping = $this->mappingFactory->create()->loadByTaxCode($taxCode);
        if ($mapping) {
            return $mapping->getXeroTaxCode();
        }
        return null;
    }
}
