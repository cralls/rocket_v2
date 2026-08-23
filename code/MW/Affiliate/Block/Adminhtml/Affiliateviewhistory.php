<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliateviewhistory extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliateviewhistory';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('View commission history : #').$this->getRequest()->getParam('orderid');

        parent::_construct();

        $this->buttonList->remove('add');
        $this->_addbackbutton();
    }
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index/');
    }
}
