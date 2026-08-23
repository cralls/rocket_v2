<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatememberpending;

use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_customerFactory = $customerFactory;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatememberpending_grid');
        $this->setDefaultSort('customer_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Pending Affiliates Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter(
                'active',
                ['in' => [
                    Statusactive::PENDING,
                    Statusactive::NOTAPPROVED,
                    Statusactive::INACTIVE
                ]]
            );

        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['email']
        );
        $collection->setOrder('main_table.customer_id', 'DESC');

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
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'customer_id'
            ]
        );
        $this->addColumn(
            'referral_name',
            [
                'header'    => __('Affiliate Name'),
                'align'     =>'left',
                'index'     => 'customer_id',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Name',
                'filter_condition_callback' => [$this, '_filterReferralnameCondition']
            ]
        );
        $this->addColumn(
            'email',
            [
                'header'    => __('Affiliate Account'),
                'align'     =>'left',
                'index'     => 'email'
            ]
        );
        $this->addColumn(
            'referral_site',
            [
                'header'    => __('Affiliate Website(s)'),
                'align'     => 'left',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Website'
            ]
        );
        $this->addColumn(
            'payment_gateway',
            [
                'header'    => __('Payment Method'),
                'align'     =>'left',
                'index'     => 'payment_gateway',
                'type'      => 'options',
                'options'   => $this->_dataHelper->_getPaymentGatewayArray()
            ]
        );
        $this->addColumn(
            'payment_email',
            [
                'header'    => __('Payment Email'),
                'align'     =>'left',
                'index'     => 'payment_email'
            ]
        );
        $this->addColumn(
            'auto_withdrawn',
            [
                'header'    => __('Withdrawal Request Method'),
                'align'     =>'left',
                'index'     => 'auto_withdrawn',
                'width'     => '30px',
                'type'      => 'options',
                'options'   => Autowithdrawn::getOptionArray()
            ]
        );
        $this->addColumn(
            'withdrawn_level',
            [
                'header'    => __('Auto payment when account balance reaches'),
                'align'     =>'left',
                'index'     => 'withdrawn_level',
                'type'      =>'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'reserve_level',
            [
                'header'    => __('Reserve Level'),
                'align'     =>'left',
                'index'     => 'reserve_level',
                'type'      =>'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'active',
            [
                'header'    => __('Active'),
                'align'     => 'left',
                'width'     => '100px',
                'index'     => 'active',
                'type'      => 'options',
                'options'   => [
                    1 => __('Pending'),
                    4 => __('Not Approved')
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
                        'url'       => ['base' => '*/*/edit'],
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
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
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

        $customerIds = [];
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

        foreach ($customerCollection as $customer) {
            $customerIds[] = $customer->getId();
        }

        $this->getCollection()->getSelect()->where("main_table.customer_id in (?)", $customerIds);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('customer_id');
        $this->getMassactionBlock()->setFormFieldName('affiliate_pending');

        $status = [
            ['value' => 1, 'label' => 'Pending'],
            ['value' => 2, 'label' => 'Approved'],
            ['value' => 4, 'label' => 'Not Approved']
        ];

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'active',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $status
                    ]
                ]
            ]
        );

        return $this;
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
