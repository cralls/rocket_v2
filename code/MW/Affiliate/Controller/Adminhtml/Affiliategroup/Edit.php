<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliategroup;

class Edit extends \MW\Affiliate\Controller\Adminhtml\Affiliategroup
{
    /**
     * Affiliate group edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $id    = $this->getRequest()->getParam('id');
        $model = $this->_groupFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Affiliate group does not exist'));
                $this->_redirect('*/*/');
                return;
            }

            $resultPage->getConfig()->getTitle()->prepend($model->getGroupName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add Affiliate Group'));
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('affiliate_data_group', $model);

        return $resultPage;
    }
}
