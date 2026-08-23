<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

class Program extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\Affiliate\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_program');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No program found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_dataHelper->getMemberProgram($this->getRequest()->getParam('id'));

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
                'align'     => 'center',
                'width'     => '50px',
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
            'start_date',
            [
                'header'    => __('Start Date'),
                'type'        => 'date',
                'width'     => '150px',
                'index'     => 'start_date'
            ]
        );
        $this->addColumn(
            'end_date',
            [
                'header'    => __('End Date'),
                'type'        => 'date',
                'width'     => '150px',
                'index'     => 'end_date'
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header'    => __('Total Commission'),
                'index'     => 'total_commission'
            ]
        );
        $this->addColumn(
            'program_position',
            [
                'header'    => __('Priority'),
                'type'      => 'number',
                'index'        => 'program_position',
                'align'        => 'center'
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
                    1 => 'Enabled',
                    2 => 'Disabled'
                ]
            ]
        );
        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption'   => __('View'),
                        'url'       => ['base' => '*/affiliateprogram/edit'],
                        'field'     => 'id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/program', ['id' => $this->getRequest()->getParam('id')]);
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

        $this->getCollection()->getSelect()->where("main_table.store_view like '%".$value."%' OR main_table.store_view = '0'");
    }
}
