<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateparent;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliateparent_grid');
        $this->setDefaultSort('customer_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Customer Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('mw_affiliate_customers');
        $collection = $this->_customerFactory->create()->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email');
        $collection->getSelect()->joinLeft(
            ['customer_affiliate' => $customerTable],
            'e.entity_id = customer_affiliate.customer_id',
            ['customer_invited']
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
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'type'  => 'number'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Affiliate Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Affiliate Email'),
                'index' => 'email'
            ]
        );
        $this->addColumn(
            'customer_invited',
            [
                'header' => __('Affiliate Parent Email'),
                'align' => 'left',
                'index' => 'customer_invited',
                'filter_condition_callback' => [$this, '_filterCustomerInvitedCondition'],
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Customerinvited'
            ]
        );
        $this->addColumn(
            'referral_name',
            [
                'header'    => __('Affiliate Parent Name'),
                'align'     =>'left',
                'index'     => 'customer_invited',
                'filter_condition_callback' => [$this, '_filterReferralnameCondition'],
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Parentname'
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

        $customerIds = [];
        $customerCollection = $this->_customerFactory->create()->getCollection()
            ->addAttributeToFilter(
                [
                    [
                        'attribute' => 'firstname',
                        ['like' => '%'.$value.'%']
                    ],
                    [
                        'attribute' => 'lastname',
                        ['like' => '%'.$value.'%']
                    ]
                ]
            );

        foreach ($customerCollection as $customer) {
            $customerIds[] = $customer->getId();
        }

        $this->getCollection()->getSelect()->where("customer_affiliate.customer_invited in (?)", $customerIds);
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

        $customerIds = [];
        $customerCollection =  $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('email', ['like' => '%'.$value.'%']);

        foreach ($customerCollection as $customer) {
            $customerIds[] = $customer->getId();
        }

        $this->getCollection()->getSelect()->where("customer_affiliate.customer_invited in (?)", $customerIds);
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customerGrid');

        $this->getMassactionBlock()->addItem(
            'parent_affiliate',
            [
                'label'    => __('Change Affiliate Parent'),
                'url'      => $this->getUrl('*/*/massParentAffiliate', ['_current' => true]),
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
            foreach ($this->_columns as $col_id => $column) {
                if (!$column->getIsSystem()) {
                    if ($col_id == 'email') {
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
