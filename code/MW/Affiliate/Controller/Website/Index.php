<?php

namespace MW\Affiliate\Controller\Website;

class Index extends \MW\Affiliate\Controller\Website
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Websites'));

        return $resultPage;
    }
}
