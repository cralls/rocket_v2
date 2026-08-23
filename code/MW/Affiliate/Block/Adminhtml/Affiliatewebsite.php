<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatewebsite extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatewebsite';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Affiliate Website');

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
