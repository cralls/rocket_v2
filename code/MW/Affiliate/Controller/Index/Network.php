<?php

namespace MW\Affiliate\Controller\Index;

class Network extends \MW\Affiliate\Controller\Index
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Network'));

        return $resultPage;
    }
}
