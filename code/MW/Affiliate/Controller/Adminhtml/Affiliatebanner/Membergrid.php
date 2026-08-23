<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class Membergrid extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
