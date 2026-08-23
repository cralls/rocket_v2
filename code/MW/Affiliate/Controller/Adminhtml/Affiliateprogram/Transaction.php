<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class Transaction extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
