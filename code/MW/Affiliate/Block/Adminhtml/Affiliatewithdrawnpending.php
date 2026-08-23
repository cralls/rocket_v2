<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatewithdrawnpending extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatewithdrawnpending';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Pending Withdrawals');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
