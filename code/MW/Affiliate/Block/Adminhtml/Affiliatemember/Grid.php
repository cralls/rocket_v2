<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember;

use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Statusreferral;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_systemStore = $systemStore;
        $this->_customerFactory = $customerFactory;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_groupFactory = $groupFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatemember_grid');
        $this->setDefaultSort('customer_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Active Affiliates Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $creditTable = $this->_resource->getTableName('mw_credit_customer');

        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('active', Statusactive::ACTIVE);
        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['website_id', 'email']
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
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Name',
                'filter_condition_callback' => [$this, '_filterReferralnameCondition']
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
            'customer_invited',
            [
                'header'    => __('Affiliate Parent'),
                'align'     => 'left',
                'index'     => 'customer_invited',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Customerinvited',
                'filter_condition_callback' => [$this, '_filterCustomerInvitedCondition']
            ]
        );

        // Affiliate group
        $groups = [];
        $groupCollection = $this->_groupFactory->create()->getCollection();
        foreach ($groupCollection as $group) {
            $groups[$group->getGroupId()] = $group->getGroupName();
        }

        $this->addColumn(
            'group_id',
            [
                'header'        => __('Affiliate Group'),
                'align'         => 'left',
                'index'         => 'group_id',
                'width'              =>  150,
                'renderer'      => 'MW\Affiliate\Block\Adminhtml\Renderer\Affiliategroup',
                'type'             => 'options',
                'options'         => $groups,
                'filter_condition_callback' => [$this, '_filterGroupCondition']
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header'    => __('Total Commission'),
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
                'align'     =>'left',
                'index'     => 'total_paid',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'credit',
            [
                'header'    => __('Current Balance'),
                'align'     =>'left',
                'index'     => 'credit',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'website_id',
                [
                    'header'    => __('Website'),
                    'align'     => 'center',
                    'width'     => '80px',
                    'type'      => 'options',
                    'options'   => $this->_systemStore->getWebsiteOptionHash(true),
                    'index'     => 'website_id'
                ]
            );
        }

        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => [
                    1 => __('Enable'),
                    2 => __('Disable')
                ]
            ]
        );
        $this->addColumn(
            'action',
            [
                'header'    =>  __('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption'   => __('View'),
                        'url'       => ['base'=> '*/*/edit'],
                        'field'     => 'id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterReferralnameCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $customerCollection = $this->_customerFactory->create()->getCollection()
            ->addAttributeToFilter([
                [
                    'attribute' => 'firstname',
                    ['like' => '%'.$value.'%']
                ],
                [
                    'attribute' => 'lastname',
                    ['like' => '%'.$value.'%']
                ]
            ]);

        $customerIds = [];
        foreach ($customerCollection as $customer) {
            $customerIds[] = $customer->getId();
        }

        $this->getCollection()->getSelect()->where("main_table.customer_id in (?)", $customerIds);
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterGroupCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $groupmemberCollection = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('group_id', ['eq' => $value]);

        $customerIds = [];
        foreach ($groupmemberCollection as $groupmember) {
            $customerIds[] = $groupmember->getCustomerId();
        }

        $this->getCollection()->getSelect()->where("main_table.customer_id in (?)", $customerIds);
    }

    /**
     * @param $collection
     * @param $column
     */
    protected function _filterCustomerInvitedCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $customerCollection = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('email', ['like' => '%'.$value.'%']);

        $customerIds = [];
        foreach ($customerCollection as $customer) {
            $customerIds[] = $customer->getId();
        }

        $this->getCollection()->getSelect()->where("main_table.customer_invited in (?)", $customerIds);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('customer_id');
        $this->getMassactionBlock()->setFormFieldName('affiliatememberGrid');

        $status = Statusreferral::getOptionArray();
        array_unshift($status, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label'=> __('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $status
                    ]
                ]
            ]
        );

        $this->getMassactionBlock()->addItem(
            'parent_affiliate',
            [
                'label'=> __('Change Affiliate Parent'),
                'url'  => $this->getUrl('*/*/massParentAffiliate', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'parent_affiliate',
                        'type' => 'text',
                        'class' => 'required-entry validate-email',
                        'label' => __('Affiliate Parent')
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    /**
     * @return string
     */
    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv.= implode(',', $data)."\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->_columns as $columnId => $column) {
                if (!$column->getIsSystem()) {
                    if ($columnId == 'email') {
                        $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getEmail()).'"';
                    } else {
                        $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($item)).'"';
                    }
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($this->getTotals())).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        return $csv;
    }
}
