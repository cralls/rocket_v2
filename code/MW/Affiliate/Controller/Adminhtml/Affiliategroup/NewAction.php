<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class NewAction extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * Create new affiliate group
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
