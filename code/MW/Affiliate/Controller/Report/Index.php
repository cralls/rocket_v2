<?php

namespace MW\Affiliate\Controller\Report;

class Index extends \MW\Affiliate\Controller\Report
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Report'));
        return $resultPage;
    }
}
