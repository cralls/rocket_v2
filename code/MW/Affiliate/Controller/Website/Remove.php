<?php

namespace MW\Affiliate\Controller\Website;

class Remove extends \MW\Affiliate\Controller\Website
{
    /**
     * Remove a website
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('id');
        if ($websiteId) {
            // Get current customer ID
            $currentCustomerId = $this->_customerSession->getCustomer()->getId();
            // Get customer ID in website member table
            $model = $this->_websitememberFactory->create()->load($websiteId);
            $websiteCustomerId = $model->getCustomerId();

            if ($currentCustomerId == $websiteCustomerId && $websiteId > 0) {
                try {
                    $model->delete();
                    $this->messageManager->addSuccess(__('Item was successfully deleted'));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*');
    }
}
