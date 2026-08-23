<?php

namespace MW\Affiliate\Controller\Index;

class Withdrawnpost extends \MW\Affiliate\Controller\Index
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $rate = $this->_pricingHelper->currency(1, false);
            $customerId = (int) $this->_customerSession->getCustomer()->getId();
            $customerWithdrawn = $this->_affiliatecustomersFactory->create()->load($customerId);
            $customerCredit = $this->_creditcustomerFactory->create()->load($customerId);
            $reserveLevel = $customerWithdrawn->getReserveLevel();
            $balance = $customerCredit->getCredit();
            $max = (double) $this->_dataHelper->getWithdrawMaxStore();
            $min = (double) $this->_dataHelper->getWithdrawMinStore();
            $withdrawAmount = (double) $this->getRequest()->getParam('withdraw_amount');

            // Convert to base currency
            $withdrawAmount = $withdrawAmount / $rate;
            if (($withdrawAmount >= $min)
                && ($withdrawAmount <= $max)
                && ($withdrawAmount + $reserveLevel <= $balance)
            ) {
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('My Withdrawal Request'));

                return $resultPage;
            } elseif ($withdrawAmount + $reserveLevel > $balance) {
                $this->messageManager->addError(__("Your balance is not enough for withdrawing"));
            } elseif (($withdrawAmount < $min) || ($withdrawAmount > $max)) {
                $this->messageManager->addError(__("Your requested amount does not match the condition"));
            }
        }

        $this->_redirect('affiliate/index/withdrawn');
    }
}
