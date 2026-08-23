<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateviewhistory;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Statusinvitation;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
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
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\Statusinvitation
     */
    protected $_statusinvitation;

    /**
     * @var \MW\Affiliate\Model\AffiliatehistoryFactory
     */
    protected $_affiliateHistory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param Statusinvitation $statusinvitation
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\Statusinvitation $statusinvitation,
        \MW\Affiliate\Model\AffiliatehistoryFactory $affiliateHistory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_dataHelper = $dataHelper;
        $this->_statusinvitation = $statusinvitation;
        $this->_affiliateHistory = $affiliateHistory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliateviewhistory_grid');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Commission History Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customer_table = $this->_resource->getTableName('customer_entity');
        $program_table =$this->_resource->getTableName('mw_affiliate_program');
        $collection = $this->_affiliateHistory->create()->getCollection()
            ->addFieldToFilter('order_id', $this->getRequest()->getParam('orderid'))
            ->setOrder('transaction_time', 'DESC')
            ->setOrder('history_id', 'DESC');
        $collection->getSelect()->join(
            ['customer_entity'=>$customer_table],
            'main_table.customer_invited = customer_entity.entity_id',
            ['email']
        );
        $collection->getSelect()->join(
            ['mw_affiliate_program'=>$program_table],
            'main_table.program_id = mw_affiliate_program.program_id',
            ['program_name']
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
                'header' => __('ID'),
                'align' => 'left',
                'index' => 'history_id'
            ]
        );

        $this->addColumn(
            'transaction_time',
            [
                'header' => __('Transaction Time'),
                'align' => 'left',
                'type'  =>  'datetime',
                'index' => 'transaction_time',
                'gmtoffset' => true,
                'default' =>  ' ---- '
            ]
        );

        $this->addColumn('program_name', [
            'header'    => __('Program Name'),
            'align'     =>'left',
            'index'     => 'program_name',
            'type'      => 'text',
        ]);

        $this->addColumn('product_id', [
            'header'    => __('Product Name'),
            'align'     =>'left',
            'index'     => 'product_id',
            'type'      => 'text',
            'renderer'  => '\MW\Affiliate\Block\Adminhtml\Renderer\Productname',
        ]);

        $this->addColumn('email', [
            'header'    => __('Affiliate Account'),
            'align'     =>'left',
            'index'     => 'email',
            'width'     => '250px',
            'type'      => 'text',
            'renderer'  => '\MW\Affiliate\Block\Adminhtml\Renderer\Emailaffiliatemember',
        ]);
        $this->addColumn('order_id', [
            'header'    =>  __('Order Number'),
            'align'     =>  'left',
            'width'        =>  100,
            'index'     =>  'order_id',
            'type'      => 'text',
            'renderer'  => '\MW\Affiliate\Block\Adminhtml\Renderer\Orderid',
        ]);

        $this->addColumn('total_amount', [
            'header'    => __('Total Amount'),
            'index'     => 'total_amount',
            'type'      =>  'price',
            'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
        ]);


        $this->addColumn('history_commission', [
            'header'    => __('Commission'),
            'index'     => 'history_commission',
            'type'      =>  'price',
            'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
        ]);


        $this->addColumn('history_discount', [
            'header'    =>  __('Customer Discount'),
            'align'     =>  'center',
            'index'     =>  'history_discount',
            'type'      =>  'price',
            'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
        ]);

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'index' => 'status',
                'type'      => 'options',
                'options'   => Status::getOptionArray()
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"'.$column->getExportHeader().'"';
            }
        }
        $csv .= implode(',', $data)."\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->_columns as $col_id => $column) {
                if (!$column->getIsSystem()) {

                    if ($col_id == 'detail') {
                        $transactionDetail = $this->_statusinvitation->getTransactionDetailCsv(
                            $item->getOrderId(),
                            $item->getEmail(),
                            $item->getStatus()
                        );
                        $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $transactionDetail).'"';
                    } else {
                        $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($item)).'"';
                    }
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($this->getTotals())).'"';
                }
            }
            $csv.= implode(',', $data)."\n";
        }

        return $csv;
    }
}
