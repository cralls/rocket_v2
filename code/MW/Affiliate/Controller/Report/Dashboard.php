<?php

namespace MW\Affiliate\Controller\Report;

class Dashboard extends \MW\Affiliate\Controller\Report
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($this->getRequest()->getPost('ajax') == 'true') {
            $data = $this->getRequest()->getParams();
            $data['customer_id'] = $this->_dataHelper->getCustomerSession()->getCustomer()->getId();
            $reportModel = $this->_dataHelper->getModel('Report');
            switch ($this->getRequest()->getParam('type')) {
                case 'dashboard':
                    //print $reportModel->prepareCollectionFrontend($data);
                    $this->getResponse()->setBody($reportModel->prepareCollectionFrontend($data));
                    break;
            }
            return;
        }
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Affiliate Report'));
        return $resultPage;
    }
}
