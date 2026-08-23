<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliategroup extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliategroup';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Manage Affiliate Groups');
        $this->_addButtonLabel = __('Add Group');

        parent::_construct();
    }
}
