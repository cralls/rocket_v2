<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class Ajaxcheckreferralcode extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Autocomplete customer email from ajax
     *
     * @return null|string
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('isAjax')) {
            return null;
        }

        $memberId = $this->getRequest()->getParam('member_id');
        $referralCode = $this->getRequest()->getParam('referral_code');

        // Check if referral code existed or not
        $collectionCustomers = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', ['neq' => $memberId])
            ->addFieldToFilter('referral_code', ['eq' => $referralCode]);

        if (count($collectionCustomers) > 0) {
            $this->getResponse()->setBody(0);
        } else {
            $this->getResponse()->setBody(1);
        }
        return;
    }
}
