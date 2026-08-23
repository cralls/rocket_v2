<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliategroup\Edit\Tab;

class Program extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_programFactory = $programFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $programIds = $this->getSelectedAddPrograms();
        $this->setId('affiliate_group_program');
        $this->setDefaultSort('program_id');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultFilter(['in_group_program' => 1]);
        if (!$programIds) {
            $this->setDefaultFilter(['in_group_program' => 0]);
        }
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_programFactory->create()->getCollection();
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
            'in_group_program',
            [
                'type'   => 'checkbox',
                'name'   => 'in_products',
                'values' => $this->_getSelectedPrograms(),
                'index'  => 'program_id'
            ]
        );
        $this->addColumn(
            'program_id',
            [
                'header' => __('ID'),
                'align' => 'left',
                'index' => 'program_id'
            ]
        );
        $this->addColumn(
            'program_name',
            [
                'header' => __('Program Name (Reset Filter to see All Programs)'),
                'align' => 'left',
                'index' => 'program_name'
            ]
        );
        $this->addColumn(
            'start_date',
            [
                'header' => __('Start Date'),
                'align' => 'left',
                'index' => 'start_date'
            ]
        );
        $this->addColumn(
            'end_date',
            [
                'header' => __('End Date'),
                'align' => 'left',
                'index' => 'end_date'
            ]
        );
        $this->addColumn(
            'total_members',
            [
                'header' => __('Total Members'),
                'align' => 'left',
                'index' => 'total_members',
                'type'  => 'number'
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header' => __('Total Commission'),
                'align' => 'left',
                'index' => 'total_commission',
                'type'  => 'price',
                'currency_code' => $this->_directoryHelper->getBaseCurrencyCode()
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'index' => 'status',
                'type'  => 'options',
                'options' => [
                    1 => __('Enabled'),
                    2 => __('Disabled')
                ]
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'left',
                'index' => 'status',
                'type'  => 'options',
                'options' => [
                    1 => __('Enabled'),
                    2 => __('Disabled')
                ]
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name' => 'position',
                'type' => 'number',
                'index' => 'position',
                'editable' => true,
                'edit_only' => true,
                'header_css_class' => 'no-display',
                'column_css_class' => 'no-display'
            ]
        );


        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedPrograms()
    {
        if ($programs = $this->getRequest()->getParam('programs_add')) {
            return $programs;
        }
        return array_keys($this->getSelectedAddPrograms());
    }


    /**
     * @return array
     */
    public function getSelectedAddPrograms()
    {
        $collection = $this->_groupprogramFactory->create()->getCollection()
            ->addFieldToFilter('group_id', $this->getRequest()->getParam('id'));
        $programs = [];

        foreach ($collection as $program) {
            $programs[$program->getProgramId()] = $program->getProgramId();
        }

        return $programs;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_group_program') {
            $programIds = $this->_getSelectedPrograms();
            if (empty($programIds)) {
                $programIds = 0;
            }
            if ($column->getFilter()->getValue() && $programIds) {
                $this->getCollection()->addFieldToFilter('program_id', ['in' => $programIds]);
            } else {
                if ($programIds) {
                    $this->getCollection()->addFieldToFilter('program_id', ['nin' => $programIds]);
                }
            }
        }
        return  parent::_addColumnFilterToCollection($column);
    }


    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') :
            $this->getUrl('*/*/programgrid', ['_current' => true]);
    }
}
