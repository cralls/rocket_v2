<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatememberpending extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatememberpending';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Pending Affiliates')
            . '<p style="width:700px;font-size:12px;color:#000">'
            . __('*Pending Affiliates will be assigned to the default group unless otherwise specified in Configuration - General Settings.')
            . '<br />'
            . __('To reassign Affiliate go to Manage Affiliate Groups')
            . '<p>';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
