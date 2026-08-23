<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliategroup;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliategroup_grid');
        $this->setDefaultSort('group_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Affiliate Group Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_groupFactory->create()->getCollection();
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
            'group_id',
            [
                'header' => __('ID'),
                'align' => 'left',
                'width' => '50px',
                'index' => 'group_id'
            ]
        );
        $this->addColumn(
            'group_name',
            [
                'header' => __('Group Name'),
                'align' => 'left',
                'index' => 'group_name'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header'    =>  __('Action'),
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
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('group_id');
        $this->getMassactionBlock()->setFormFieldName('group_id');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => __('Are you sure?')
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
