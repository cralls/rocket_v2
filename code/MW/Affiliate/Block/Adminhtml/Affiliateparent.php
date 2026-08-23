<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliateparent extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliateparent';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Manage Customers');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
