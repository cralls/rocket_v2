<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatemember extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatemember';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Active Affiliates');
        $this->_addButtonLabel = __('Add Affiliate');

        parent::_construct();
    }
}
