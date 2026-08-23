<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class NewAction extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Create new affiliate program
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
