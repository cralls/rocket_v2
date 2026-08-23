<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatememberpending;

class Index extends \MW\Affiliate\Controller\Adminhtml\Affiliatememberpending
{
    /**
     * Manage Pending Affiliates page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Pending Affiliates'));

        return $resultPage;
    }
}
