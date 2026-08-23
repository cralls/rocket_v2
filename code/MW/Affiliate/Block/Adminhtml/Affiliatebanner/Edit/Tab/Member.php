<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatebanner\Edit\Tab;

use MW\Affiliate\Model\Statusactive;

class Member extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \MW\Affiliate\Model\AffiliatebannermemberFactory
     */
    protected $_bannermemberFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomerFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \MW\Affiliate\Model\AffiliatebannermemberFactory $bannermemberFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomerFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\Affiliate\Model\AffiliatebannermemberFactory $bannermemberFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomerFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_bannermemberFactory = $bannermemberFactory;
        $this->_affiliatecustomerFactory = $affiliatecustomerFactory;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_banner_member');
        $this->setDefaultSort('customer_id');
        $this->setUseAjax(true);
        $collection = $this->_bannermemberFactory->create()->getCollection()
            ->addFieldToFilter('banner_id', $this->getRequest()->getParam('id'));
        if (sizeof($collection) > 0) {
            $this->setDefaultFilter(['in_banner_member' => 1]);
        }
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $creditTable = $this->_resource->getTableName('mw_credit_customer');

        $collection = $this->_affiliatecustomerFactory->create()->getCollection()
            ->addFieldToFilter('active', Statusactive::ACTIVE);
        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['email']
        );
        $collection->getSelect()->join(
            ['mw_credit_customer' => $creditTable],
            'main_table.customer_id = mw_credit_customer.customer_id',
            ['credit']
        );

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
            'in_banner_member',
            [
                'type'      => 'checkbox',
                'name'      => 'in_products',
                'values'    => $this->_getSelectedMembers(),
                'align'     => 'center',
                'index'     => 'customer_id'
            ]
        );
        $this->addColumn(
            'customer_id',
            [
                'header'    => __('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'customer_id'
            ]
        );
        $this->addColumn(
            'referral_name',
            [
                'header'    => __('Affiliate Name'),
                'align'     => 'left',
                'index'     => 'customer_id',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header'    => __('Affiliate Account'),
                'align'     => 'left',
                'index'     => 'email'
            ]
        );
        $this->addColumn(
            'credit',
            [
                'header'    => __('Current Balance'),
                'align'     => 'left',
                'index'     => 'credit',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header'    => __('Commission'),
                'align'     => 'left',
                'index'     => 'total_commission',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'total_paid',
            [
                'header'    => __('Total Paid Out'),
                'align'     => 'left',
                'index'     => 'total_paid',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'credit',
            [
                'header'    => __('Current Balance'),
                'align'     => 'left',
                'index'     => 'credit',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => true,
                'header_css_class' => 'no-display',
                'column_css_class' => 'no-display'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedMembers()
    {
        $members = array_keys($this->getSelectedAddMembers());

        return $members;
    }

    /**
     * @return array
     */
    public function getSelectedAddMembers()
    {
        $collection = $this->_bannermemberFactory->create()->getCollection()
            ->addFieldToFilter('banner_id', $this->getRequest()->getParam('id'));
        $members = [];

        foreach ($collection as $member) {
            $members[$member->getCustomerId()] = $member->getCustomerId();
        }

        return $members;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_banner_member') {
            $memberIds = $this->_getSelectedMembers();
            if (empty($memberIds)) {
                $memberIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.customer_id', ['in' => $memberIds]);
            } else {
                if ($memberIds) {
                    $this->getCollection()->addFieldToFilter('main_table.customer_id', ['nin' => $memberIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/membergrid', ['_current' => true]);
    }
}
