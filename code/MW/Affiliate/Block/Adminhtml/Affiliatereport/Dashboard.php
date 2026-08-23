<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatereport;

class Dashboard extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_affiliatereport_dashboard';
        $this->_headerText = __('Dashboard');
        $this->_blockGroup = 'MW_Affiliate';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
