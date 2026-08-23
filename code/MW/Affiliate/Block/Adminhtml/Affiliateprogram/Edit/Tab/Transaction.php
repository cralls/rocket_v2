<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab;

use MW\Affiliate\Model\Status;

class Transaction extends \Magento\Backend\Block\Widget\Grid\Extended
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
     * @var \MW\Affiliate\Model\AffiliatehistoryFactory
     */
    protected $_historyFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\AffiliatehistoryFactory $historyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\AffiliatehistoryFactory $historyFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_historyFactory = $historyFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_program_transaction');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Affiliate Transaction Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_historyFactory->create()->getCollection()
            ->addFieldToFilter('program_id', $this->getRequest()->getParam('id'))
            ->addFieldToFilter('program_id', ['neq' => 0])
            ->setOrder('transaction_time', 'DESC')
            ->setOrder('history_id', 'DESC');

        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_invited = customer_entity.entity_id',
            ['email']
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
            'history_id',
            [
                'header'    =>  __('ID'),
                'align'     =>  'left',
                'index'     =>  'history_id'
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
            'product_id',
            [
                'header'    => __('Product Name'),
                'align'     => 'left',
                'index'     => 'product_id',
                'type'      => 'text',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Productname'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header'    => __('Affiliate Account'),
                'align'     => 'left',
                'index'     => 'email',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Emailaffiliatemember'
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header'    => __('Order Number'),
                'align'     => 'left',
                'index'     => 'order_id',
                'type'      => 'text',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Orderid'
            ]
        );
        $this->addColumn(
            'total_amount',
            [
                'header'    => __('Product Price'),
                'index'     => 'total_amount',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'history_commission',
            [
                'header'    => __('Affiliate Commission'),
                'index'     => 'history_commission',
                'type'      => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'history_discount',
            [
                'header'    => __('Customer Discount'),
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
                'align'     =>'center',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Status::getOptionArray()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/transaction', ['id' => $this->getRequest()->getParam('id')]);
    }
}
