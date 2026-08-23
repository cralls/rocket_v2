<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatememberpending;

class Edit extends \MW\Affiliate\Controller\Adminhtml\Affiliatememberpending
{
    /**
     * Pending Affiliates edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $id    = $this->getRequest()->getParam('id');
        $model = $this->_affiliatecustomersFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Member does not exist'));
                $this->_redirect('*/*/');
                return;
            }

            $customer = $this->_customerFactory->create()->load($id);
            $resultPage->getConfig()->getTitle()->prepend($customer->getName());
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('affiliate_data_member', $model);

        return $resultPage;
    }
}
