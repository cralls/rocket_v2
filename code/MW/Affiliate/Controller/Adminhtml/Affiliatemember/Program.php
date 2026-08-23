<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class Program extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
