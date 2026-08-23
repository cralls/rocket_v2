<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatebanner;

use MW\Affiliate\Model\Statusprogram;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Model\AffiliatebannerFactory
     */
    protected $_bannerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_bannerFactory = $bannerFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatebanner_grid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Affiliate Banner Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_bannerFactory->create()->getCollection();
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
            'banner_id',
            [
                'header'    => __('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'banner_id'
            ]
        );
        $this->addColumn(
            'image_name',
            [
                'header'    => __('Image'),
                'width'     => '75px',
                'index'     => 'image_name',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Image'
            ]
        );
        $this->addColumn(
            'title_banner',
            [
                'header'    => __('Title'),
                'align'     => 'left',
                'index'     => 'title_banner'
            ]
        );
        $this->addColumn(
            'link_banner',
            [
                'header'    => __('Banner Link'),
                'align'     => 'left',
                'index'     => 'link_banner'
            ]
        );
        $this->addColumn(
            'width',
            [
                'header'    => __('Width (pixel)'),
                'width'     => '150px',
                'index'     => 'width'
            ]
        );
        $this->addColumn(
            'height',
            [
                'header'    => __('Height (pixel)'),
                'width'     => '150px',
                'index'     => 'height'
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
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('banner_id');
        $this->getMassactionBlock()->setFormFieldName('affiliatebannerGrid');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => __('Are you sure?')
            ]
        );

        $status = Statusprogram::getOptionArray();
        array_unshift($status, ['label' => '', 'value'=> '']);

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

        return $this;
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
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }
}
