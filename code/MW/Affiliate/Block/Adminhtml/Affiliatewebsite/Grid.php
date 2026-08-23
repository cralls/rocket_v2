<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatewebsite;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatewebsitememberFactory
     */
    protected $_affiliatewebsiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatewebsitememberFactory $affiliatewebsiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatewebsitememberFactory $affiliatewebsiteFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatewebsiteFactory = $affiliatewebsiteFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatewebsite_grid');
        $this->setDefaultSort('affiliate_website_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Website Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_affiliatewebsiteFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['email' => 'customer_entity.email']
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
            'affiliate_website_id',
            [
                'header' => __('ID'),
                'align' => 'left',
                'index' => 'affiliate_website_id'
            ]
        );
        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer Email'),
                'align' => 'left',
                'index' => 'email'
            ]
        );
        $this->addColumn(
            'website',
            [
                'header' => __('Website'),
                'align' => 'left',
                'index' => 'domain_name'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'index' => 'status',
                'type'      => 'options',
                'options'   => [
                    1 => __('Verified'),
                    0 => __('Not Verified')
                ]
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
        $this->setMassactionIdField('affiliate_website_id');
        $this->getMassactionBlock()->setFormFieldName('affiliatewebsiteGrid');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => __('Are you sure?')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label'=> __('Change status'),
                'url'  => $this->getUrl('*/*/updateStatus', ['_current' => true]),
                'additional' => [
                    'visibility'    => [
                        'name'      => 'status',
                        'type'     => 'select',
                        'class'  => 'required-entry',
                        'label'  => __('Status'),
                        'values' => [
                            1 => __('Verified'),
                            0 => __('Not Verified')
                        ]
                    ]
                ]
            ]
        );

        return $this;
    }
}
