<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatewebsite;

class MassDelete extends \MW\Affiliate\Controller\Adminhtml\Affiliatewebsite
{
    /**
     * Delete multi websites action
     */
    public function execute()
    {
        $affiliatewebsiteIds = $this->getRequest()->getParam('affiliatewebsiteGrid');
        if (!is_array($affiliatewebsiteIds)) {
            $this->messageManager->addError(__('Please select item(s)'));
        } else {
            $websiteCollection = $this->_objectManager->get('MW\Affiliate\Model\Affiliatewebsitemember')
                ->getCollection()
                ->addFieldToFilter('affiliate_website_id', ['in' => $affiliatewebsiteIds]);

            try {
                if ($websiteCollection->getSize() > 0) {
                    foreach ($websiteCollection as $website) {
                        $website->delete();
                    }

                    $this->messageManager->addSuccess(
                        __('Total of %1 record(s) were successfully deleted', count($websiteCollection->getSize()))
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
