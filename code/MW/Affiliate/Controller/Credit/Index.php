<?php

namespace MW\Affiliate\Controller\Credit;

class Index extends \MW\Affiliate\Controller\Credit\IndexAbstract
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Credit'));

        return $resultPage;
    }
}
