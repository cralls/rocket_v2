<?php

namespace MW\Affiliate\Controller\Index;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Transactiontype;
use MW\Affiliate\Model\Orderstatus;

class Withdrawnsubmit extends \MW\Affiliate\Controller\Index
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $customerId = (int) $this->_customerSession->getCustomer()->getId();
            $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customerId);
            $post = $this->getRequest()->getParams();
            $withdrawnAmount = doubleval($post["withdraw_amount"]);

            $fee = $this->_dataHelper->getFeePaymentGateway($affiliateCustomer->getPaymentGateway());
            if (strpos($fee, '%') === true) {
                $percent = doubleval(substr($fee, 0, strpos($fee, '%')));
                $fee = ($percent * $withdrawnAmount) / 100;
            } else {
                $fee = doubleval($fee);
            }

            $withdrawnReceive = $withdrawnAmount - $fee;
            $paymentGateway = $affiliateCustomer->getPaymentGateway();
            $paymentEmail = $affiliateCustomer->getPaymentEmail();

            if ($paymentGateway == 'banktransfer') {
                $paymentEmail = '';
            }

            $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
            $withData = [
                'customer_id'        => $customerId,
                'payment_gateway'    => $paymentGateway,
                'payment_email'        => $paymentEmail,
                'bank_name'            => $affiliateCustomer->getBankName(),
                'name_account'        => $affiliateCustomer->getNameAccount(),
                'bank_country'        => $affiliateCustomer->getBankCountry(),
                'swift_bic'            => $affiliateCustomer->getSwiftBic(),
                'account_number'    => $affiliateCustomer->getAccountNumber(),
                're_account_number'    => $affiliateCustomer->getReAccountNumber(),
                'withdrawn_amount'    => $withdrawnAmount,
                'fee'                => $fee,
                'amount_receive'    => $withdrawnReceive,
                'status'            => Status::PENDING,
                'withdrawn_time'    => $now
            ];

            $this->_withdrawnFactory->create()->setData($withData)->save();

            // Update affiliate customer table (total_paid)
            $oldTotalPaid = $affiliateCustomer->getTotalPaid();
            $newTotalPaid = $oldTotalPaid + $withdrawnAmount;
            $newTotalPaid = round($newTotalPaid, 2);
            $affiliateCustomer->setData('total_paid', $newTotalPaid)->save();

            // Update credit customer table
            $creditcustomer = $this->_creditcustomerFactory->create()->load($customerId);
            $oldCredit = $creditcustomer->getCredit();
            $amount = -$withdrawnAmount;
            $newCredit = $oldCredit + $amount;
            $newCredit = round($newCredit, 2);
            $creditcustomer->setCredit($newCredit)->save();

            $withdrawn = $this->_withdrawnFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('withdrawn_id', 'DESC')
                ->getFirstItem();

            $historyData = [
                'customer_id'            => $customerId,
                'type_transaction'        => Transactiontype::WITHDRAWN,
                'status'                => Orderstatus::PENDING,
                'transaction_detail'    => $withdrawn->getWithdrawnId(),
                'amount'                => $amount,
                'beginning_transaction' => $oldCredit,
                'end_transaction'        => $newCredit,
                'created_time'            => $now
            ];
            $this->_credithistoryFactory->create()->setData($historyData)->save();

            $withdrawnAmountCurrency = $this->_pricingHelper->currency($withdrawnAmount, true, false);

            // Send notification email to customer when request withdrawal manually
            $storeCode = $this->_storeManager->getStore()->getCode();
            $this->_dataHelper->sendMailCustomerRequestWithdrawn($customerId, $withdrawnAmount, $storeCode);

            $this->messageManager->addSuccess(__("You have requested to withdraw: %1", $withdrawnAmountCurrency));
            $this->_redirect('affiliate/index/withdrawn');
        }
    }
}
