<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatehistory;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_controller = 'adminhtml_affiliatehistory';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('delete');
    }
}
