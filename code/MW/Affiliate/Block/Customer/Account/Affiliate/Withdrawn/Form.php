<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate\Withdrawn;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_pricingHelper = $pricingHelper;
        $this->_countryFactory = $countryFactory;
        $this->_customerSession = $customerSession;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_creditcustomerFactory = $creditcustomerFactory;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * @param $customerId
     * @return \MW\Affiliate\Model\Affiliatecustomers
     */
    public function getAffiliateMember($customerId)
    {
        return $this->_affiliatecustomersFactory->create()->load($customerId);
    }

    /**
     * @param $customerId
     * @return \MW\Affiliate\Model\Creditcustomer
     */
    public function getCreditCustomer($customerId)
    {
        return $this->_creditcustomerFactory->create()->load($customerId);
    }

    /**
     * @param $bankCountry
     * @return string
     */
    public function getBankCountryName($bankCountry)
    {
        return $this->_countryFactory->create()->load($bankCountry)->getName();
    }

    /**
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @param $value
     * @param bool|true $format
     * @param bool|true $includeContainer
     * @return float|string
     */
    public function getCurrency($value, $format = true, $includeContainer = true)
    {
        return $this->_pricingHelper->currency($value, $format, $includeContainer);
    }
}
