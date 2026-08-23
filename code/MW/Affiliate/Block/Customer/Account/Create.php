<?php

namespace MW\Affiliate\Block\Customer\Account;

class Create extends \Magento\Directory\Block\Data
{
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
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
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
     * @return \MW\Affiliate\Model\Affiliatecustomers
     */
    public function getAffiliateMemberModel()
    {
        return $this->_affiliatecustomersFactory->create();
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function getCustomerCredit($customerId)
    {
        return $this->_creditcustomerFactory->create()->load($customerId);
    }
}
