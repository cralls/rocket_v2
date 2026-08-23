<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatecredithistory extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatecredithistory';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Transaction History');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
