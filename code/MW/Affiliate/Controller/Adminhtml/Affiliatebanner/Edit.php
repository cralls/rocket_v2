<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class Edit extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Affiliate banner edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $id    = $this->getRequest()->getParam('id');
        $model = $this->_bannerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Affiliate banner does not exist'));
                $this->_redirect('*/*/');
                return;
            }

            $resultPage->getConfig()->getTitle()->prepend($model->getTitleBanner());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add Banner'));
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('affiliate_data_banner', $model);

        return $resultPage;
    }
}
