<?php

namespace MW\Affiliate\Controller\Banner;

class Index extends \MW\Affiliate\Controller\Banner
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Banners'));

        return $resultPage;
    }
}
