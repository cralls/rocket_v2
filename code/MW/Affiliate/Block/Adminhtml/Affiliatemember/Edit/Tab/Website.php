<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

class Website extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \MW\Affiliate\Model\AffiliatewebsitememberFactory
     */
    protected $_websitememberFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_websitememberFactory = $websitememberFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_website');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No website found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collections = $this->_websitememberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $this->getRequest()->getParam('id')]);

        $collections->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['email' => 'customer_entity.email']
        );

        $this->setCollection($collections);
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
                'header'    => __('ID'),
                'align'     => 'center',
                'width'     => '50px',
                'index'     => 'affiliate_website_id'
            ]
        );
        $this->addColumn(
            'website',
            [
                'header'    => __('Website'),
                'align'     => 'left',
                'index'     => 'domain_name'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'width'     => '150px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => [
                    1 => 'Verified',
                    0 => 'Not Verified'
                ]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/website', ['id' => $this->getRequest()->getParam('id')]);
    }
}
