<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

use MW\Affiliate\Model\Statusreferral;

class MassStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Update status for multi Affiliate
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('affiliatememberGrid');

        if ($customerIds) {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($customerIds as $customerId) {
                    $affiliate = $this->_affiliatecustomersFactory->create()->load($customerId);
                    $affiliateStatus = $affiliate->getStatus();

                    if ($status == Statusreferral::LOCKED && $affiliateStatus == Statusreferral::ENABLED) {
                        // Send notification email when the affiliate is locked
                        $this->_dataHelper->sendMailAffiliateIsLocked($customerId);
                    } elseif ($status == Statusreferral::ENABLED && $affiliateStatus == Statusreferral::LOCKED) {
                        // Send notification email when the affiliate is unlocked
                        $this->_dataHelper->sendMailAffiliateIsUnLocked($customerId);
                    }

                    // Save new status for current affiliate
                    $affiliate->setStatus($status)->save();
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($customerIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
