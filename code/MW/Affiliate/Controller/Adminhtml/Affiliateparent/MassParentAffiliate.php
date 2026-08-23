<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateparent;

use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Typeinvitation;

class MassParentAffiliate extends \MW\Affiliate\Controller\Adminhtml\Affiliateparent
{
    /**
     * Change Affiliate parent action
     */
    public function execute()
    {
        $customerIds = $this->getRequest()->getParam('customerGrid');
        if ($customerIds) {
            try {
                $parentAffiliateId = 0;
                $parentAffiliate = $this->getRequest()->getParam('parent_affiliate');

                if (isset($parentAffiliate) && $parentAffiliate != '') {
                    // Check parent affiliate is exist or not
                    $affiliateFilters = $this->_customerFactory->create()->getCollection()
                        ->addFieldToFilter('email', $parentAffiliate);

                    if (sizeof($affiliateFilters) > 0) {
                        foreach ($affiliateFilters as $affiliateFilter) {
                            if ($this->_dataHelper->getActiveAndEnableAffiliate($affiliateFilter->getId()) == 1) {
                                $parentAffiliateId = $affiliateFilter->getId();
                            } else {
                                $this->messageManager->addError(__('Affiliate parent invalid'));
                                $this->_redirect('*/*/');
                                return;
                            }
                            break;
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
                        // Check parent affiliate is exist or not
                        // For creating new record or update old record in mw_affiliate_customers table
                        $affiliateCollection = $this->_affiliatecustomersFactory->create()->getCollection()
                            ->addFieldToFilter('customer_id', $customerId);

                        if (sizeof($affiliateCollection) > 0) {
                            $this->_affiliatecustomersFactory->create()->load($customerId)
                                ->setCustomerInvited($parentAffiliateId)
                                ->save();
                        } else {
                            $customerData = [
                                'customer_id' => $customerId,
                                'active' => Statusactive::INACTIVE,
                                'payment_gateway' => '',
                                'payment_email' => '',
                                'auto_withdrawn' => 0,
                                'withdrawn_level' => 0,
                                'reserve_level' => 0,
                                'total_commission' => 0,
                                'total_paid' => 0,
                                'referral_code' => '',
                                'bank_name' => '',
                                'name_account' => '',
                                'bank_country' => '',
                                'swift_bic' => '',
                                'account_number' => '',
                                're_account_number' => '',
                                'referral_site' => '',
                                'customer_invited' => $parentAffiliateId,
                                'invitation_type' => Typeinvitation::NON_REFERRAL,
                                'customer_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                                'status' => 1
                            ];

                            $this->_affiliatecustomersFactory->create()
                                ->setData($customerData)
                                ->setId($customerId)
                                ->save();
                        }
                    }
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', $count)
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
