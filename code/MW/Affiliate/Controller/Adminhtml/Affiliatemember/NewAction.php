<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class NewAction extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Add new Active Affiliate page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
