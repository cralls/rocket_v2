<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate;

use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Statusreferral;

class Network extends \Magento\Framework\View\Element\Template
{
    protected $_arrayResult = [];

    protected $_arrayNetworkTable = [];

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomerFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_entityFactory = $entityFactory;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
        $this->_affiliatecustomerFactory = $affiliatecustomersFactory;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'customer_affiliate_history_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getAffiliate());

        return $this;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function getAffiliate()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $this->showSubAffiliateNetworkTable($customerId, '', 0);

        $collection = new \Magento\Framework\Data\Collection($this->_entityFactory);
        foreach ($this->_arrayResult as $row) {
            $rowObj = new \Magento\Framework\DataObject();
            $rowObj->setData($row);
            $collection->addItem($rowObj);
        }

        // Set data for display via frontend
        return $collection;
    }

    /**
     * @param $customerId
     * @param $referral
     * @param $i
     */
    public function showSubAffiliateNetworkTable($customerId, $referral, $i)
    {
        if (!in_array($customerId, $this->_arrayNetworkTable)) {
            $this->_arrayNetworkTable[] = $customerId;
            $customerChilds = $this->getAffiliateParents($customerId);
            $customer = $this->_customerFactory->create()->load($customerId);
            $affiliateCustomer = $this->_affiliatecustomerFactory->create()->load($customerId);
            $statusOptions = Statusactive::getOptionArray();

            if ($referral != '') {
                $this->_arrayResult[] = [
                    'level'            => $i,
                    'name'            => $customer->getName(),
                    'email'            => $customer->getEmail(),
                    'referral'        => $referral,
                    'commission'    => $affiliateCustomer->getTotalCommission(),
                    'joined_date'    => $affiliateCustomer->getCustomerTime(),
                    'status'        => $statusOptions[$affiliateCustomer->getActive()]
                ];
            }

            if (sizeof($customerChilds) > 0) {
                $i++;
                foreach ($customerChilds as $customerChild) {
                    $this->showSubAffiliateNetworkTable($customerChild, $customer->getName(), $i);
                }
            }
        }
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getAffiliateParents($customerId)
    {
        $result = [];

        if ($customerId) {
            $collection = $this->_affiliatecustomerFactory->create()->getCollection()
                ->addFieldToFilter('customer_invited', $customerId)
                ->addFieldToFilter('status', Statusreferral::ENABLED)
                ->addFieldToFilter('active', Statusactive::ACTIVE);

            $result = array_diff($collection->getAllIds(), [$customerId]);
        }

        return $result;
    }

    /**
     * Retrive collection from toolbar
     */
    public function getCollection()
    {
        return $this->getToolbar()->getCollection();
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getToolbar()->toHtml();
    }

    /**
     * @param $level
     * @return string
     */
    public function showNumberArrows($level)
    {
        $arrow = $imageArrow = '';

        for ($i=2; $i <= (int)$level; $i++) {
            if ($i == 2) {
                $imageArrow = 'MW_Affiliate::images/line.gif';
            }
            if ($i > 2) {
                $imageArrow = 'MW_Affiliate::images/line2.gif';
            }

            $arrow .= '<img src="' . $this->getViewFileUrl($imageArrow) . '" />';
        }

        return $arrow;
    }
}
