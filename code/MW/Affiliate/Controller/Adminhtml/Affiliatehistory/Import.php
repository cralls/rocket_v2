<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatehistory;

class Import extends \MW\Affiliate\Controller\Adminhtml\Affiliatehistory
{
    /**
     * Import transactions page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Update Affiliate Transactions via CSV'));

        return $resultPage;
    }
}
