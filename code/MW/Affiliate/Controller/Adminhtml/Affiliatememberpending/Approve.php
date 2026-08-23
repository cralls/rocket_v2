<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatememberpending;

use MW\Affiliate\Model\Statusactive;

class Approve extends \MW\Affiliate\Controller\Adminhtml\Affiliatememberpending
{
    /**
     * Approve pending Affiliate action
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('id');

        if ($customerId) {
            $this->_affiliatecustomersFactory->create()->load($customerId)
                ->setActive(Statusactive::ACTIVE)
                ->setCustomerTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()))
                ->save();

            // Re-set referral code for affiliate customers
            $this->_dataHelper->setReferralCode($customerId);

            // Auto assign this member to default group
            $storeId = $this->_customerFactory->create()->load($customerId)->getStoreId();
            $store = $this->_storeFactory->create()->load($storeId);
            $this->_dataHelper->setMemberDefaultGroupAffiliate($customerId, $store->getCode());

            // Send mail when admin approve
            $this->_dataHelper->sendMailCustomerActiveAffiliate($customerId);

            // Set total member customer program
            $this->_dataHelper->setTotalMemberProgram();
            $this->messageManager->addSuccess(__('Total of %1 record(s) were successfully updated', 1));
        }

        $this->_redirect('*/*/');
    }
}
