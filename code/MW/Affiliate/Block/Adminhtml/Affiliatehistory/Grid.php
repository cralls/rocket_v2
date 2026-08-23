<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatehistory;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Transactiontype;
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
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \MW\Affiliate\Model\Statusinvitation
     */
    protected $_statusinvitation;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory
     * @param Statusinvitation $statusinvitation
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory,
        \MW\Affiliate\Model\Statusinvitation $statusinvitation,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_dataHelper = $dataHelper;
        $this->_transactionFactory = $transactionFactory;
        $this->_statusinvitation = $statusinvitation;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatehistory_grid');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Commission History Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_transactionFactory->create()->getCollection()
            ->addFieldtoFilter('customer_invited', 0)
            ->setOrder('transaction_time', 'DESC')
            ->setOrder('history_id', 'DESC');
        $collection->getSelect()->joinLeft(
            ['customer_entity' => $customerTable],
            'main_table.show_customer_invited = customer_entity.entity_id',
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
        $this->addColumn(
            'commission_type',
            [
                'header' => __('Commission Type'),
                'align' => 'left',
                'index' => 'commission_type',
                'type' => 'options',
                'options' => Transactiontype::getAffiliateTypeArray()
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Affiliate Account'),
                'align' => 'left',
                'index' => 'email'
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header' => __('Affiliate Commission'),
                'index' => 'total_commission',
                'type' =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'total_discount',
            [
                'header' => __('Customer Discount'),
                'index' => 'total_discount',
                'type' =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'grand_total',
            [
                'header' => __('Purchase Total'),
                'align' => 'right',
                'index' => 'grand_total',
                'type' =>  'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Purchasehistory',
                'filter' => false,
                'sortable'  => false
            ]
        );
        $this->addColumn(
            'detail',
            [
                'header' => __('Detail'),
                'align' => 'left',
                'index' => 'detail',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Affiliatetransaction',
                'filter' => false,
                'sortable'  => false
            ]
        );
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
        $this->addColumn(
            'action',
            [
                'header'    => __('Action'),
                'type'      => 'action',
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
                'renderer'    => 'MW\Affiliate\Block\Adminhtml\Renderer\Invitationaction'
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
        $this->setMassactionIdField('history_id');
        $this->getMassactionBlock()->setFormFieldName('mw_history_id');

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
                        'values' => Status::getOptionAction()
                    ]
                ]
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
        if ($row->getStatus() == Statusinvitation::PURCHASE) {
            return $this->getUrl('affiliate/affiliateviewhistory/', ['orderid' => $row->getOrderId()]);
        }

        return '';
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
