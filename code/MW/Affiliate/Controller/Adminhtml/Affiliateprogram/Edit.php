<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class Edit extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Affiliate program edit page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MW_Affiliate::affiliate');
        $id    = $this->getRequest()->getParam('id');
        $model = $this->_programFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Program does not exist'));
                $this->_redirect('*/*/');
                return;
            }

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            $model->getActions()->setJsFormObject('rule_actions_fieldset');

            $resultPage->getConfig()->getTitle()->prepend($model->getProgramName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add Program'));
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('affiliate_data_program', $model);

        return $resultPage;
    }
}
