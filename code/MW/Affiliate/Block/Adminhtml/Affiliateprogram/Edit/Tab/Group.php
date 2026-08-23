<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab;

class Group extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        $this->_groupFactory = $groupFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_program_group');
        $this->setDefaultSort('group_id');
        $this->setUseAjax(true);
        $collection = $this->_groupprogramFactory->create()->getCollection()
            ->addFieldToFilter('program_id', $this->getRequest()->getParam('id'));
        if (sizeof($collection) > 0) {
            $this->setDefaultFilter(['in_program_group' => 1]);
        }
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_groupFactory->create()->getCollection();

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
            'in_program_group',
            [
                'type'      => 'checkbox',
                'name'      => 'in_program_group',
                'values'    => $this->_getSelectedGroups(),
                'align'     => 'center',
                'index'     => 'group_id'
            ]
        );
        $this->addColumn(
            'group_id',
            [
                'header'    => __('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'group_id'
            ]
        );
        $this->addColumn(
            'group_name',
            [
                'header'    => __('Add Group to Affiliate Program (Reset Filter to see ALL Groups)'),
                'align'     => 'left',
                'index'     => 'group_name'
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
    protected function _getSelectedGroups()
    {
        $groups = array_keys($this->getSelectedAddGroups());

        return $groups;
    }

    /**
     * @return array
     */
    public function getSelectedAddGroups()
    {
        $collection = $this->_groupprogramFactory->create()->getCollection()
            ->addFieldToFilter('program_id', $this->getRequest()->getParam('id'));
        $groups = [];

        foreach ($collection as $group) {
            $groups[$group->getGroupId()] = $group->getGroupId();
        }

        return $groups;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_program_group') {
            $groupIds = $this->_getSelectedGroups();
            if (empty($groupIds)) {
                $groupIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('group_id', ['in' => $groupIds]);
            } else {
                if ($groupIds) {
                    $this->getCollection()->addFieldToFilter('group_id', ['nin' => $groupIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url') :
            $this->getUrl('*/*/groupgrid', ['_current' => true]);
    }
}
