<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Statusreferral;
use MW\Affiliate\Model\Statusactive;

class Network extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_arrayResult = [];

    protected $_arrayNetworkTable = [];

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory
     */
    protected $_entityFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_entityFactory = $entityFactory;
        $this->_customerFactory = $customerFactory;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_network');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Sub-affiliate found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $this->showSubAffiliateNetworkTable($this->getRequest()->getParam('id'), '', 0);
        $collection = new \Magento\Framework\Data\Collection($this->_entityFactory);

        foreach ($this->_arrayResult as $row) {
            $rowObj = new \Magento\Framework\DataObject();
            $rowObj->setData($row);
            $collection->addItem($rowObj);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'level',
            [
                'header'    => __('Level'),
                'align'     => 'center',
                'index'     => 'level',
                'width'        => '10'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header'    => __('Name'),
                'align'     => 'left',
                'index'     => 'name'
            ]
        );
        $this->addColumn(
            'Email',
            [
                'header'    => __('Email'),
                'index'     => 'email'
            ]
        );
        $this->addColumn(
            'commission',
            [
                'header'        => __('Commission'),
                'index'         => 'commission',
                'type'          => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'referred_by',
            [
                'header'    => __('Referred by'),
                'align'     => 'left',
                'index'     => 'referral'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'align'     => 'center',
                'index'     => 'status',
                'width'        => '10'
            ]
        );
        $this->addColumn(
            'joined_date',
            [
                'header'    => __('Joined Date'),
                'type'      => 'datetime',
                'align'     => 'center',
                'index'     => 'joined_date',
                'gmtoffset' => true,
                'default'   => ' ---- '
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param $customerId
     * @return array
     */
    public function getAffiliateParents($customerId)
    {
        $result = [];
        if ($customerId) {
            $collection = $this->_affiliatecustomersFactory->create()->getCollection()
                ->addFieldToFilter('customer_invited', $customerId)
                ->addFieldToFilter('status', Statusreferral::ENABLED)
                ->addFieldToFilter('active', Statusactive::ACTIVE);

            $result = array_diff($collection->getAllIds(), [$customerId]);
        }

        return $result;
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
            $customerChildIds = $this->getAffiliateParents($customerId);
            $customer = $this->_customerFactory->create()->load($customerId);
            $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customerId);
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

            if (sizeof($customerChildIds) > 0) {
                $i++;
                foreach ($customerChildIds as $customerChildId) {
                    $this->showSubAffiliateNetworkTable($customerChildId, $customer->getName(), $i);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/network', ['id' => $this->getRequest()->getParam('id')]);
    }
}
