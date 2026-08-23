<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Membergrid extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
