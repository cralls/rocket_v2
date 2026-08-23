<?php

namespace MW\Affiliate\Controller\Index;

use MW\Affiliate\Model\Autowithdrawn;

class Referral extends \MW\Affiliate\Controller\Index
{
    /**
     * Save Affiliate account information
     */
    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $max = (double) $this->_dataHelper->getWithdrawMaxStore();
        $min = (double) $this->_dataHelper->getWithdrawMinStore();
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $getwayWithdrawn = $param['getway_withdrawn'];
        $paymentEmail = $param['paypal_email'];

        if ($getwayWithdrawn != 'banktransfer' && $getwayWithdrawn != 'check') {
            $collection = $this->_affiliatecustomersFactory->create()->getCollection()
                ->addFieldToFilter('payment_email', $paymentEmail);

            if ($collection->getSize() > 0) {
                foreach ($collection as $affiliate) {
                    if ($affiliate->getCustomerId() != $customerId) {
                        $this->messageManager->addError(__('There is already an account with this emails paypal'));
                        $this->_redirect('affiliate/index/referralaccount');
                        return;
                    }
                }
            }
        }

        $autoWithdrawn = (int) $param['auto_withdrawn'];
        $paymentReleaseLevel = (double) $param['payment_release_level'];
        if ($autoWithdrawn == Autowithdrawn::AUTO) {
            if ($paymentReleaseLevel < $min || $paymentReleaseLevel > $max) {
                $this->messageManager->addError(__('Please insert a value of Auto payment when account balance reaches that is in range of [%1, %2]', $min, $max));
                $this->_redirect('affiliate/index/referralaccount');
                return;
            }
        }

        $reserveLevel = $param['reserve_level'];
        if (!$reserveLevel) {
            $reserveLevel = 0;
        }

        if ($getwayWithdrawn == 'check') {
            $paymentEmail = '';
        }

        $bankName = '';
        $nameAccount = '';
        $bankCountry = '';
        $swiftBic = '';
        $accountNumber = '';
        $reAccountNumber = '';
        if ($getwayWithdrawn == 'banktransfer') {
            $paymentEmail = '';
            $bankName = $param['bank_name'];
            $nameAccount = $param['name_account'];
            $bankCountry = $param['bank_country'];
            $swiftBic = $param['swift_bic'];
            $accountNumber = $param['account_number'];
            $reAccountNumber = $param['re_account_number'];
        }

        if ($autoWithdrawn == Autowithdrawn::MANUAL) {
            $paymentReleaseLevel = 0;
        }

        $affiliate = $this->_affiliatecustomersFactory->create()->load($customerId);
        $affiliate->setPaymentGateway($getwayWithdrawn);
        $affiliate->setPaymentEmail($paymentEmail);
        $affiliate->setAutoWithdrawn($autoWithdrawn);
        $affiliate->setWithdrawnLevel($paymentReleaseLevel);
        $affiliate->setReserveLevel($reserveLevel);
        $affiliate->setBankName($bankName);
        $affiliate->setNameAccount($nameAccount);
        $affiliate->setBankCountry($bankCountry);
        $affiliate->setSwiftBic($swiftBic);
        $affiliate->setAccountNumber($accountNumber);
        $affiliate->setReAccountNumber($reAccountNumber);

        try {
            $affiliate->save();
            $this->messageManager->addSuccess(__("You have successfully updated affiliate account"));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('affiliate/index/referralaccount');
    }
}
