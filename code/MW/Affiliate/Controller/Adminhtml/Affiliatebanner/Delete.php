<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class Delete extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Delete Affiliate banner
     */
    public function execute()
    {
        $bannerId = $this->getRequest()->getParam('id');
        if ($bannerId) {
            try {
                $model = $this->_bannerFactory->create();
                $model->setId($bannerId)->delete();
                $this->messageManager->addSuccess(__('The banner has successfully deleted'));
                $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $bannerId]);
            }
        }

        $this->_redirect('*/*/');
    }
}
