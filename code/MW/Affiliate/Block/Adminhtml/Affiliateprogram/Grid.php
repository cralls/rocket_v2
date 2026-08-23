<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram;

use MW\Affiliate\Model\Statusprogram;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_directoryHelper = $directoryHelper;
        $this->_programFactory = $programFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliateprogram_grid');
        $this->setDefaultSort('program_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Affiliate Program Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_programFactory->create()->getCollection();
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
            'program_id',
            [
                'header'    => __('ID'),
                'align'     => 'right',
                'index'     => 'program_id'
            ]
        );
        $this->addColumn(
            'program_name',
            [
                'header'    => __('Program Name'),
                'align'     => 'left',
                'index'     => 'program_name'
            ]
        );
        $this->addColumn(
            'affiliate_commission',
            [
                'header'    => __('Affiliate Commission'),
                'align'     => 'center',
                'index'     => 'commission'
            ]
        );
        $this->addColumn(
            'customer_discount',
            [
                'header'    => __('Customer Discount'),
                'align'     => 'center',
                'index'     => 'discount'
            ]
        );
        $this->addColumn(
            'start_date',
            [
                'header'    => __('Start Date'),
                'width'     => '150px',
                'index'     => 'start_date',
                'type'      => 'date'
            ]
        );
        $this->addColumn(
            'end_date',
            [
                'header'    => __('End Date'),
                'width'     => '150px',
                'index'     => 'end_date',
                'type'      => 'date'
            ]
        );
        $this->addColumn(
            'total_members',
            [
                'header'    => __('Total Members'),
                'align'     => 'left',
                'type'      => 'number',
                'index'     => 'total_members'
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header'    => __('Total Commission'),
                'align'     => 'left',
                'type'      => 'price',
                'index'     => 'total_commission',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'program_position',
            [
                'header'    => __('Priority'),
                'align'     => 'left',
                'type'      => 'number',
                'index'     => 'program_position'
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_view',
                [
                    'header'        => __('Store View'),
                    'index'         => 'store_view',
                    'type'          => 'store',
                    'store_all'     => true,
                    'store_view'    => true,
                    'sortable'      => false,
                    'renderer'      => 'MW\Affiliate\Block\Adminhtml\Renderer\Storeview',
                    'filter_condition_callback' => [$this, '_filterStoreCondition']
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
                    1 => __('Enabled'),
                    2 => __('Disabled')
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
                        'caption'   => __('Edit'),
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
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->getSelect()->where("main_table.store_view like '%".$value."%' or main_table.store_view = '0'");
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('program_id');
        $this->getMassactionBlock()->setFormFieldName('affiliateprogramGrid');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => __('Are you sure?')
            ]
        );

        $status = Statusprogram::getOptionArray();
        array_unshift($status, ['label' => '', 'value' => '']);

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url'      => $this->getUrl('*/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name'  => 'status',
                        'type'  => 'select',
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
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
