<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatereport;

class Referralsite extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_affiliatereport_referralsite';
        $this->_headerText = __('Affiliate Website(s) Statistic');
        $this->_blockGroup = 'MW_Affiliate';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
