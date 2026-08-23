<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Statuswithdraw;

class Withdrawn extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_withdrawnFactory = $withdrawnFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_member_withdrawn');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Withdrawal Transaction Found'));
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_withdrawnFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $this->getRequest()->getParam('id'))
            ->setOrder('withdrawn_time', 'DESC');

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
                'header'    => __('ID'),
                'align'     => 'left',
                'index'     => 'withdrawn_id',
                'name'        => 'withdrawn_id',
                'width'     => 15
            ]
        );
        $this->addColumn(
            'withdrawn_time',
            [
                'header'    => __('Withdrawal Time'),
                'type'      => 'datetime',
                'align'     => 'center',
                'index'     => 'withdrawn_time',
                'width'     => 100
            ]
        );
        $this->addColumn(
            'withdrawn_amount',
            [
                'header'    => __('Withdrawal Amount'),
                'align'     => 'left',
                'type'      => 'price',
                'index'     => 'withdrawn_amount',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'fee',
            [
                'header'    => __('Payment Processing Fee'),
                'align'     => 'left',
                'type'      => 'price',
                'index'     => 'fee',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'amount_receive',
            [
                'header'    => __('Net Amount'),
                'align'     => 'center',
                'type'      => 'price',
                'index'     => 'amount_receive',
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
                'options'   => Status::getOptionArray(),
                'width'     => 100
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('withdrawn_id');
        $this->getMassactionBlock()->setFormFieldName('affiliate_member_withdrawn')
            ->setTemplate('MW_Affiliate::widget/grid/massaction_extended.phtml');

        $this->getMassactionBlock()->addItem(
            'mass_status',
            [
                'label'=> __('Change status'),
                'url'  => $this->getUrl('affiliate/affiliatemember/withdrawnstatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'mass_status',
                        'type' => 'select',
                        'label' => __('Status'),
                        'values' => Statuswithdraw::getOptionArray()
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('affiliate/affiliatemember/withdrawn', ['id' => $this->getRequest()->getParam('id')]);
    }
}
