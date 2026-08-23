<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Transactiontype;

class Credithistory extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliatecredit_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Transaction History Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');

        $collection = $this->_credithistoryFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))
            ->setOrder('created_time', 'DESC')
            ->setOrder('credit_history_id', 'DESC');

        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
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
            'credit_history_id',
            [
                'header'    =>  __('ID'),
                'align'     =>  'left',
                'index'     =>  'credit_history_id',
                'width'     =>  10
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header'    =>  __('Transaction Time'),
                'type'      =>  'datetime',
                'align'     =>  'center',
                'index'     =>  'created_time',
                'gmtoffset' => true,
                'default'   =>  ' ---- '
            ]
        );
        $this->addColumn(
            'type_transaction',
            [
                'header'    => __('Type of Transaction'),
                'align'     =>'left',
                'index'     => 'type_transaction',
                'width'     => '250px',
                'type'      => 'options',
                'options'   => Transactiontype::getOptionArray()
            ]
        );
        $this->addColumn(
            'transaction_detail',
            [
                'header'    =>  __('Transaction Detail'),
                'align'     =>  'left',
                'width'        =>  400,
                'index'     =>  'credit_history_id',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Credittransaction'
            ]
        );
        $this->addColumn(
            'amount',
            [
                'header'    =>  __('Amount'),
                'align'     =>  'center',
                'index'     =>  'amount',
                'type'      =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'end_transaction',
            [
                'header'    => __('Balance'),
                'index'     => 'end_transaction',
                'type'      =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/credithistory', ['id' => $this->getRequest()->getParam('id')]);
    }
}
