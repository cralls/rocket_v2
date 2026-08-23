<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatememberpending;

use MW\Affiliate\Model\Statusactive;

class MassStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatememberpending
{
    /**
     * Update status for multi Affiliate
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('affiliate_pending');

        if ($customerIds) {
            $status = $this->getRequest()->getParam('active');
            try {
                foreach ($customerIds as $customerId) {
                    $this->_affiliatecustomersFactory->create()->load($customerId)
                        ->setActive($status)
                        ->save();

                    if ($status == Statusactive::ACTIVE) {
                        // Re-set referral code for affiliate customers
                        $this->_dataHelper->setReferralCode($customerId);

                        // Auto assign this affiliate to default group
                        $storeId = $this->_customerFactory->create()->load($customerId)->getStoreId();
                        $storeCode = $this->_storeFactory->create()->load($storeId)->getCode();
                        $this->_dataHelper->setMemberDefaultGroupAffiliate($customerId, $storeCode);

                        // Send notification email when admin approves for this affiliate
                        $this->_dataHelper->sendMailCustomerActiveAffiliate($customerId);

                    } elseif ($status == Statusactive::NOTAPPROVED) {
                        // Send notification email when admin does not approve for this affiliate
                        $this->_dataHelper->sendMailCustomerNotActiveAffiliate($customerId);
                    }
                }

                // Set total member customer program
                $this->_dataHelper->setTotalMemberProgram();

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
