<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Status;

class Viewtransaction extends \Magento\Backend\Block\Widget\Grid\Extended
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
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatehistoryFactory
     */
    protected $_historyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \MW\Affiliate\Model\AffiliatehistoryFactory $historyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \MW\Affiliate\Model\AffiliatehistoryFactory $historyFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_productFactory = $productFactory;
        $this->_historyFactory = $historyFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_viewtransaction');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Affiliate Transaction Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $programTable = $this->_resource->getTableName('mw_affiliate_program');

        $collection = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('customer_invited', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('order_id', $this->getRequest()->getParam('orderid'))
            ->setOrder('transaction_time', 'DESC')
            ->setOrder('history_id', 'DESC');

        $collection->getSelect()->join(
            ['mw_affiliate_program' => $programTable],
            'main_table.program_id = mw_affiliate_program.program_id',
            ['program_name']
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header'    => __('ID'),
                'align'     => 'left',
                'index'     => 'history_id',
                'width'     => 10
            ]
        );
        $this->addColumn(
            'transaction_time',
            [
                'header'    => __('Transaction Time'),
                'type'      => 'datetime',
                'align'     => 'center',
                'index'     => 'transaction_time',
                'gmtoffset' => true,
                'default'   => ' ---- '
            ]
        );
        $this->addColumn(
            'program_name',
            [
                'header'    => __('Program Name'),
                'align'     => 'left',
                'index'     => 'program_name',
                'type'      => 'text'
            ]
        );
        $this->addColumn(
            'product_id',
            [
                'header'    => __('Product Name'),
                'align'     => 'left',
                'index'     => 'product_id',
                'type'      => 'options',
                'options'   => $this->__getNameProduct()
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header'    => __('Order Number'),
                'align'     => 'left',
                'index'     => 'order_id',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Orderid'
            ]
        );
        $this->addColumn(
            'total_amount',
            [
                'header'    => __('Total Amount By Product'),
                'index'     => 'total_amount',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'history_commission',
            [
                'header'    => __('Commission'),
                'index'     => 'history_commission',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'history_discount',
            [
                'header'    => __('Discount'),
                'align'     => 'center',
                'index'     => 'history_discount',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'align'     => 'center',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Status::getOptionArray()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    private function __getNameProduct()
    {
        $result = [];
        $collection = $this->_productFactory->create()->getCollection()
            ->addAttributeToSelect('name');

        foreach ($collection as $item) {
            $result[$item->getId()] = $item->getName();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'affiliate/affiliatemember/viewtransaction',
            [
                'id' => $this->getRequest()->getParam('id'),
                'orderid' => $this->getRequest()->getParam('orderid')
            ]
        );
    }
}
