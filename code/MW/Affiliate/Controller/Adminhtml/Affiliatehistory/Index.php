<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatehistory;

class Index extends \MW\Affiliate\Controller\Adminhtml\Affiliatehistory
{
    /**
     * Commission History page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Commission History'));

        return $resultPage;
    }
}
