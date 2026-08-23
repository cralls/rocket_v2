<?php

namespace MW\Affiliate\Controller\Index;

class Listprogram extends \MW\Affiliate\Controller\Index
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Programs'));

        return $resultPage;
    }
}
