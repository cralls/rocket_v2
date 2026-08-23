<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class NewAction extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Create new affiliate banner
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
