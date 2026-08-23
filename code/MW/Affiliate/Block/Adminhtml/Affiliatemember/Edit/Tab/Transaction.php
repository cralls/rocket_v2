<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Transactiontype;

class Transaction extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_transactionFactory = $transactionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_transaction');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Commission History Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_transactionFactory->create()->getCollection()
            ->addFieldToFilter('customer_invited', $this->getRequest()->getParam('id'))
            ->setOrder('transaction_time', 'DESC')
            ->setOrder('history_id', 'DESC');

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
                'header'    => __('ID'),
                'align'     => 'center',
                'width'     => '50px',
                'index'     => 'history_id'
            ]
        );
        $this->addColumn(
            'transaction_time',
            [
                'header'    =>  __('Transaction Time'),
                'type'      =>  'datetime',
                'align'     =>  'center',
                'index'     =>  'transaction_time',
                'width'        =>  150,
                'gmtoffset' => true,
                'default'   =>  ' ---- '
            ]
        );
        $this->addColumn(
            'commission_type',
            [
                'header'    => __('Commission Type'),
                'align'     => 'left',
                'index'     => 'commission_type',
                'type'      => 'options',
                'options'   => Transactiontype::getAffiliateTypeArray()
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header'    => __('Affiliate Commission'),
                'index'     => 'total_commission',
                'width'        =>  '90',
                'type'      =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'total_discount',
            [
                'header'    =>  __('Customer Discount'),
                'align'     =>  'center',
                'width'        =>  '90',
                'index'     =>  'total_discount',
                'type'      =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'grand_total',
            [
                'header'    => __('Purchase Total'),
                'align'     =>'right',
                'index'     => 'grand_total',
                'width'        =>  '100',
                'type'      =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Purchasehistory',
                'filter'    => false,
                'sortable'  => false
            ]
        );
        $this->addColumn(
            'detail',
            [
                'header'    => __('Detail'),
                'align'     => 'left',
                'renderer'    => 'MW\Affiliate\Block\Adminhtml\Renderer\Affiliatetransaction'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header'    => __('Status'),
                'align'     =>'center',
                'width'        =>  '100',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Status::getOptionArray()
            ]
        );
        $this->addColumn(
            'action',
            [
                'header'    =>  __('Action'),
                'width'     => '60',
                'align'        => 'center',
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'renderer'    => 'MW\Affiliate\Block\Adminhtml\Renderer\Transactionmemberaction'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/transaction', ['id' => $this->getRequest()->getParam('id')]);
    }
}
