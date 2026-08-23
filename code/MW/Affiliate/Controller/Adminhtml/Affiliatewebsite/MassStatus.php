<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatewebsite;

class MassStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatewebsite
{
    /**
     * Update website status action
     */
    public function execute()
    {
        $affiliatewebsiteIds = $this->getRequest()->getParam('affiliatewebsiteGrid');
        if (!is_array($affiliatewebsiteIds)) {
            $this->messageManager->addError(__('Please select item(s)'));
        } else {
            $newStatus = $this->getRequest()->getParam('status');
            try {
                foreach ($affiliatewebsiteIds as $affiliatewebsiteId) {
                    $this->_objectManager->get('MW\Affiliate\Model\Affiliatewebsitemember')
                        ->load($affiliatewebsiteId)
                        ->setStatus($newStatus)
                        ->save();
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($affiliatewebsiteIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
