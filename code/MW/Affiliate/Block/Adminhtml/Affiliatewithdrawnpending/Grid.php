<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatewithdrawnpending;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Statuswithdraw;

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
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_directoryHelper = $directoryHelper;
        $this->_dataHelper = $dataHelper;
        $this->_withdrawnFactory = $withdrawnFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatewithdrawnpending_grid');
        $this->setDefaultSort('withdrawn_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setEmptyText(__('No Pending Withdrawals History'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $collection = $this->_withdrawnFactory->create()->getCollection()
            ->addFieldToFilter('status', ['eq' => Status::PENDING])
            ->setOrder('withdrawn_time', 'DESC')
            ->setOrder('withdrawn_id', 'DESC');
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
            'withdrawn_id',
            [
                'header' => __('ID'),
                'align'  => 'left',
                'index'  => 'withdrawn_id',
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Affiliate Account'),
                'align'  => 'left',
                'index'  => 'email',
                'type'   => 'text',
                'renderer'  => 'MW\Affiliate\Block\Adminhtml\Renderer\Emailaffiliatemember'
            ]
        );
        $this->addColumn(
            'payment_gateway',
            [
                'header' => __('Payment Method'),
                'align'  => 'left',
                'index'  => 'payment_gateway',
                'type'   => 'options',
                'options' => $this->_dataHelper->_getPaymentGatewayArray(),
                'filter' => false,
                'sortable' => false
            ]
        );
        $this->addColumn(
            'payment_email',
            [
                'header' => __('Payment Email'),
                'align'  => 'left',
                'index'  => 'payment_email',
                'type'   => 'text'
            ]
        );
        $this->addColumn(
            'withdrawn_time',
            [
                'header' => __('Withdrawal Time'),
                'align'  => 'center',
                'index'  => 'withdrawn_time',
                'type'   => 'datetime'
            ]
        );
        $this->addColumn(
            'withdrawn_amount',
            [
                'header' => __('Withdrawal Amount'),
                'align'  => 'left',
                'index'  => 'withdrawn_amount',
                'type'   => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
            ]
        );
        $this->addColumn(
            'fee',
            [
                'header' => __('Payment Processing Fee'),
                'align'  => 'left',
                'index'  => 'fee',
                'type'   => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
            ]
        );
        $this->addColumn(
            'amount_receive',
            [
                'header' => __('Net Amount'),
                'align'  => 'center',
                'index'  => 'amount_receive',
                'type'   => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode(),
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align'  => 'center',
                'index'  => 'status',
                'type'   => 'options',
                'options' => Status::getOptionArray()
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
        $this->setMassactionIdField('withdrawn_id');
        $this->getMassactionBlock()->setFormFieldName('affiliate_withdrawn_pending');

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label'      => __('Change status'),
                'url'        => $this->getUrl('*/*/withdrawnedit', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => __('Status'),
                        'values' => Statuswithdraw::getOptionArray()
                    ]
                ]
            ]
        );

        return $this;
    }

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
        $headerItems = [
            0 => __('"ID"'),
            1 => __('"Affiliate Account"'),
            2 => __('"Payment Method"'),
            3 => __('"Payment Email"'),
            4 => __('"Bank Name"'),
            5 => __('"Name on Account"'),
            6 => __('"Account Number"'),
            7 => __('"Bank Country"'),
            8 => __('"SWIFT code"'),
            9 => __('"Withdrawal Time"'),
            10 => __('"Withdrawal Amount"'),
            11 => __('"Payment Processing Fee"'),
            12 => __('"Net Amount"'),
            13 => __('"Status"')
        ];
        $data = $headerItems;
        $csv .= implode(',', $data)."\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getWithdrawnId()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getEmail()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getPaymentGateway()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getPaymentEmail()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getBankName()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getNameAccount()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getAccountNumber()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getBankCountry()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getSwiftBic()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getWithdrawnTime()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getWithdrawnAmount()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getFee()).'"';
            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $item->getAmountReceive()).'"';

            $getStatus = '';
            switch ($item->getStatus()) {
                case 1:
                    $getStatus = __('Pending');
                    break;
                case 2:
                    $getStatus = __('Complete');
                    break;
                case 3:
                    $getStatus = __('Canceled');
                    break;
                case 4:
                    $getStatus = __('Closed');
                    break;
                case 6:
                    $getStatus = __('Holding');
                    break;
            }

            $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $getStatus).'"';
            $csv .= implode(',', $data)."\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->_columns as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"'.str_replace(['"', '\\'], ['""', '\\\\'], $column->getRowFieldExport($this->getTotals())).'"';
                }
            }
            $csv .= implode(',', $data)."\n";
        }

        return $csv;
    }
}
