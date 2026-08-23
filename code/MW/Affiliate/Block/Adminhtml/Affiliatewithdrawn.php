<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatewithdrawn extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatewithdrawn';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('All Withdrawals History');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
