<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

class Overview extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Dashboard report page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($this->getRequest()->getPost('ajax') == 'true') {
            $data = $this->getRequest()->getParams();
            $reportModel = $this->_objectManager->get('MW\Affiliate\Model\Report');

            switch ($this->getRequest()->getParam('type')) {
                case 'dashboard':
                    //print $reportModel->prepareCollection($data);
                    $this->getResponse()->setBody($reportModel->prepareCollection($data));
                    break;
            }
            return;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::overview');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate Dashboard'));
        return $resultPage;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::overview');
    }
}
