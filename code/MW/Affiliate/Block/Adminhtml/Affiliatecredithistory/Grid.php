<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatecredithistory;

use MW\Affiliate\Model\Orderstatus;
use MW\Affiliate\Model\Transactiontype;

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
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \MW\Affiliate\Model\Typecsv
     */
    protected $_creditTypecsv;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param \MW\Affiliate\Model\Typecsv $creditTypecsv
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \MW\Affiliate\Model\Typecsv $creditTypecsv,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_creditTypecsv = $creditTypecsv;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatecredithistory_grid');
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
            ->addFieldToFilter('status', ['neq' => Orderstatus::HOLDING])
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
                'header' => __('ID'),
                'align' => 'left',
                'index' => 'credit_history_id'
            ]
        );
        $this->addColumn(
            'created_time',
            [
                'header' => __('Transaction Time'),
                'align' => 'left',
                'type'  =>  'datetime',
                'index' => 'created_time',
                'gmtoffset' => true,
                'default' =>  ' ---- '
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Affiliate Account'),
                'align' => 'left',
                'index' => 'email',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Emailaffiliatemember'
            ]
        );
        $this->addColumn(
            'type_transaction',
            [
                'header' => __('Type of Transaction'),
                'align' => 'left',
                'index' => 'type_transaction',
                'type' => 'options',
                'options' => Transactiontype::getOptionArray()
            ]
        );
        $this->addColumn(
            'transaction_detail',
            [
                'header' => __('Transaction Detail'),
                'index' => 'transaction_detail',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Credittransaction',
                'filter' => false,
            ]
        );
        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'index' => 'amount',
                'type' =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'end_transaction',
            [
                'header' => __('Affiliate Balance'),
                'align' => 'right',
                'index' => 'end_transaction',
                'type' =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
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
            foreach ($this->_columns as $columnId => $column) {
                if (!$column->getIsSystem()) {
                    if ($columnId == 'email') {
                        $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getEmail()).'"';
                    } elseif ($columnId == 'transaction_detail') {
                        $transactionDetail = $this->_creditTypecsv->getTransactionDetail(
                            $item->getTypeTransaction(),
                            $item->getTransactionDetail()
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
