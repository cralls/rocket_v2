<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatewithdrawnpending;

class Index extends \MW\Affiliate\Controller\Adminhtml\Affiliatewithdrawnpending
{
    /**
     * Pending Withdrawals page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Pending Withdrawals'));

        return $resultPage;
    }
}
