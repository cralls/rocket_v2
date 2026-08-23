<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

class Referralsite extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Affiliate website(s) report page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Website(s) Statistic'));

        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::referralsite');
    }
}
