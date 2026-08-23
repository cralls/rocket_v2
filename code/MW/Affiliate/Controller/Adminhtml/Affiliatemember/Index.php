<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class Index extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Manage Active Affiliates page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Active Affiliates'));

        return $resultPage;
    }
}
