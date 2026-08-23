<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliateprogram extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliateprogram';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Manage Programs');
        $this->_addButtonLabel = __('Add Program');

        parent::_construct();
    }
}
