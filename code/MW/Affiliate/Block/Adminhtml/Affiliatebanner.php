<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatebanner extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatebanner';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Banner Manager');
        $this->_addButtonLabel = __('Add Banner');

        parent::_construct();
    }
}
