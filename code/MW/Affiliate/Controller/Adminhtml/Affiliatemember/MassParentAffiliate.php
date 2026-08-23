<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class MassParentAffiliate extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Update parent for multi Affiliate
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('affiliatememberGrid');

        if ($customerIds) {
            $parentAffiliateId = 0;
            $parentAffiliate = $this->getRequest()->getParam('parent_affiliate');

            try {
                if (isset($parentAffiliate) && $parentAffiliate != '') {
                    $customer = $this->_customerFactory->create()->getCollection()
                        ->addFieldToFilter('email', $parentAffiliate)
                        ->getFirstItem();

                    if ($customer) {
                        $available = $this->_dataHelper->getActiveAndEnableAffiliate($customer->getId());

                        if ($available) {
                            $parentAffiliateId = $customer->getId();
                        } else {
                            $this->messageManager->addError(__('Affiliate parent invalid'));
                            $this->_redirect('*/*/');
                            return;
                        }
                    } else {
                        $this->messageManager->addError(__('Affiliate parent invalid'));
                        $this->_redirect('*/*/');
                        return;
                    }
                }

                $count = 0;
                foreach ($customerIds as $customerId) {
                    if ($parentAffiliateId && $parentAffiliateId != $customerId) {
                        $count++;
                        $this->_affiliatecustomersFactory->create()->load($customerId)
                            ->setCustomerInvited($parentAffiliateId)
                            ->save();
                    }
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
