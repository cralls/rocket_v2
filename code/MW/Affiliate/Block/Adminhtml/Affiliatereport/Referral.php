<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatereport;

class Referral extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_affiliatereport_referral';
        $this->_headerText = __('Affiliate Invitation Statistic');
        $this->_blockGroup = 'MW_Affiliate';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
