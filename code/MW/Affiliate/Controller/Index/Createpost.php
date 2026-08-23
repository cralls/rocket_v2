<?php

namespace MW\Affiliate\Controller\Index;

class Createpost extends \MW\Affiliate\Controller\Createaccount
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $store_id = $this->_storeManager->getStore()->getId();
        $storeCode = $this->_storeManager->getStore($store_id)->getCode();
        $session = $this->sessionManager;
        $session->unsetData('check_affiliate');
        $session->unsetData('payment_gateway');
        $session->unsetData('payment_email');
        $session->unsetData('auto_withdrawn');
        $session->unsetData('withdrawn_level');
        $session->unsetData('reserve_level');
        $session->unsetData('bank_name');
        $session->unsetData('name_account');
        $session->unsetData('bank_country');
        $session->unsetData('swift_bic');
        $session->unsetData('referral_site');

        $max = (double)$this->_dataHelper->getWithdrawMaxStore($storeCode);
        $min = (double)$this->_dataHelper->getWithdrawMinStore($storeCode);
        $payment_email = $this->getRequest()->getPost('paypal_email');
        $payment_email = $payment_email?$payment_email:'';
        $getway_withdrawn = $this->getRequest()->getPost('getway_withdrawn');
        $getway_withdrawn = $getway_withdrawn?$getway_withdrawn:'';
        $check_affiliate = $this->getRequest()->getPost('check_affiliate');
        $reserve_level = $this->getRequest()->getPost('reserve_level');
        $auto_withdrawn = (int)$this->getRequest()->getPost('auto_withdrawn');
        $auto_withdrawn = $auto_withdrawn?$auto_withdrawn:0;
        $payment_release_level = (double)$this->getRequest()->getPost('payment_release_level');
        $bank_name = '';
        $name_account = '';
        $bank_country = '';
        $swift_bic = '';
        $account_number = '';
        $re_account_number = '';
        $referral_site = '';
        $referral_site = $this->getRequest()->getPost('referral_site');

        if ($getway_withdrawn == 'check') {
            $payment_email = '';
        }
        if ($getway_withdrawn == 'banktransfer') {
            $payment_email = '';
            $bank_name = $this->getRequest()->getPost('bank_name');
            $name_account = $this->getRequest()->getPost('name_account');
            $bank_country = $this->getRequest()->getPost('bank_country');
            $swift_bic = $this->getRequest()->getPost('swift_bic');
            $account_number = $this->getRequest()->getPost('account_number');
            $re_account_number = $this->getRequest()->getPost('re_account_number');
        }

            // set session
            $session->setCheckAffiliate($check_affiliate);
            $session->setPaymentGateway($getway_withdrawn);
            $session->setPaymentEmail($payment_email);
            $session->setAutoWithdrawn($auto_withdrawn);
            $session->setBankName($bank_name);
            $session->setNameAccount($name_account);
            $session->setBankCountry($bank_country);
            $session->setSwiftBic($swift_bic);

        if ($referral_site) {
            $session->setReferralSite($referral_site);
        }
        if ($payment_release_level) {
            $session->setWithdrawnLevel($payment_release_level);
        }
        if ($reserve_level) {
            $session->setReserveLevel($reserve_level);
        }
        if ($this->_dataHelper->getStoreConfig('affiliate/money/enable_withdrawal')) {
            if ($getway_withdrawn != 'banktransfer' && $getway_withdrawn != 'check') {

                $collectionFilter = $this->_affiliatecustomersFactory->create()->getCollection()
                    ->addFieldToFilter('payment_email', $payment_email);
                if (sizeof($collectionFilter) > 0) {
                    $this->messageManager->addError(__('There is already an account with this emails paypal'));
                    $this->_redirect('affiliate/index/createaccount/');
                    return;
                }
            }
        }

        if ($auto_withdrawn == \MW\Affiliate\Model\Autowithdrawn::AUTO) {
            if ($payment_release_level < $min || $payment_release_level > $max) {
                $this->messageManager->addError(__('Please insert a value of Auto payment when account balance reaches that is in range of [%s, %s]', $min, $max));
                $this->_redirect('affiliate/index/createaccount/');
                return;
            }
        }

        if (!$reserve_level) {
            $reserve_level = 0;
        }
        if ($auto_withdrawn == \MW\Affiliate\Model\Autowithdrawn::MANUAL) {
            $payment_release_level = 0;
        }

            $customer_id = (int)$this->_customerSession->getCustomer()->getId();
            $active = \MW\Affiliate\Model\Statusactive::PENDING;

            // neu cau hinh config tu approved
            $auto_approved = $this->_dataHelper->getAutoApproveRegisterStore($storeCode);
        if ($auto_approved) {
            $active = \MW\Affiliate\Model\Statusactive::ACTIVE;
        }
            $customers = $this->_affiliatecustomersFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $customer_id);
        if (sizeof($customers) > 0) {
            $referral_edit = $this->_affiliatecustomersFactory->create()->load($customer_id);
            $referral_edit->setActive($active);
            $referral_edit->setPaymentGateway($getway_withdrawn);
            $referral_edit->setPaymentEmail($payment_email);
            $referral_edit->setAutoWithdrawn($auto_withdrawn);
            $referral_edit->setWithdrawnLevel($payment_release_level);
            $referral_edit->setReserveLevel($reserve_level);
            $referral_edit->setBankName($bank_name);
            $referral_edit->setNameAccount($name_account);
            $referral_edit->setBankCountry($bank_country);
            $referral_edit->setSwiftBic($swift_bic);
            $referral_edit->setAccountNumber($account_number);
            $referral_edit->setReAccountNumber($re_account_number);
            //$referral_edit->setReferralSite($referral_site);
            $referral_edit->save();
        }
            // trong truong hop dk la thanh vien website nhung ko luu vao bang affiliatecustomer
        elseif (sizeof($customers) == 0) {
            $cokie = (int)$this->_dataHelper->getCookie('customer');
            // neu khong ton tai cokie cua thanh vien gioi thieu . gan cookie = 0
            if ($cokie) {
                if ($this->_dataHelper->getLockAffiliate($cokie)== 1) {
                    $cokie = 0;
                }
            } else {
                $cokie = 0;
            };
            $invitation_type = \MW\Affiliate\Model\Typeinvitation::NON_REFERRAL;
            if ($cokie != 0) {
                $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK;
            }

            // Save affiliate customers to db
            $customerData = [
                'customer_id'            => $customer_id,
                'active'                => $active,
                'payment_gateway'        => $getway_withdrawn,
                'payment_email'        => $payment_email,
                'auto_withdrawn'        => $auto_withdrawn,
                'withdrawn_level'        => $payment_release_level,
                'reserve_level'        => $reserve_level,
                'bank_name'            => $bank_name,
                'name_account'        => $name_account,
                'bank_country'        => $bank_country,
                'swift_bic'            => $swift_bic,
                'account_number'        => $account_number,
                're_account_number'    => $re_account_number,
                'referral_site'        => $referral_site,
                'total_commission'    => 0,
                'total_paid'            => 0,
                'referral_code'        => '',
                'status'                => 1,
                'invitation_type'        => $invitation_type,
                'customer_time'         => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                'customer_invited'    => $cokie
            ];

            $this->_affiliatecustomersFactory->create()->setData($customerData)->save();
            // Save affiliate websites to db
            if ($referral_site) {
                $sites = explode(',', $referral_site);
                $websiteModel = $this->_dataHelper->getModel('Affiliatewebsitemember');
                foreach ($sites as $url) {
                    $siteItem = [
                        'customer_id'   => $customer_id,
                        'domain_name'    => trim($url),
                        'verified_key'  => $this->_dataHelper->getWebsiteVerificationKey(trim($url)),
                        'status'        => \MW\Affiliate\Model\Statuswebsite::UNVERIFIED
                    ];
                    $websiteModel->setData($siteItem);
                    $websiteModel->save();
                }
            }

            // update affiliate invitation
            $clientIP = $this->getRequest()->getServer('REMOTE_ADDR');
            $referral_from = $this->_dataHelper->getCookie('mw_referral_from');
            $referral_to = $this->_dataHelper->getCookie('mw_referral_to');
            $referral_from_domain = $this->_dataHelper->getCookie('mw_referral_from_domain');

            if (!$referral_from) {
                $referral_from = '';
            }
            if (!$referral_to) {
                $referral_to = '';
            }
            if (!$referral_from_domain) {
                $referral_from_domain = '';
            }
            if ($cokie != 0) {
                $this->_dataHelper->updateAffiliateInvitionNew(
                    $customer_id,
                    $cokie,
                    $clientIP,
                    $referral_from,
                    $referral_from_domain,
                    $referral_to,
                    $invitation_type
                );
            }
        }

            // xoa session di
            $session->unsetData('check_affiliate');
            $session->unsetData('payment_gateway');
            $session->unsetData('payment_email');
            $session->unsetData('auto_withdrawn');
            $session->unsetData('withdrawn_level');
            $session->unsetData('reserve_level');
            $session->unsetData('bank_name');
            $session->unsetData('name_account');
            $session->unsetData('bank_country');
            $session->unsetData('swift_bic');
            $session->unsetData('referral_site');

        if ($active == \MW\Affiliate\Model\Statusactive::PENDING) {
            // gui mail cho khach hang khi dang ky lam thanh vien affiliate
            $this->_dataHelper->sendEmailCustomerPending($customer_id);
            // gui mail cho admin chiu trach nhiem active customer affiliate
            $this->_dataHelper->sendEmailAdminActiveAffiliate($customer_id);
        } elseif ($active == \MW\Affiliate\Model\Statusactive::ACTIVE) {
            // set lai referral code cho cac customer affiliate
            $this->_dataHelper->setReferralCode($customer_id);
            $store_id = $this->customerFactory->create()->load($customer_id)->getStoreId();
            $this->_storeManager->getStore($store_id)->getId();
            $this->_dataHelper->setMemberDefaultGroupAffiliate($customer_id, $storeCode);
            //gui mail khi admin dong y cho gia nhap vao affiliate
            $this->_dataHelper->sendMailCustomerActiveAffiliate($customer_id);
        }


            $creditcustomer = $this->_creditcustomerFactory->create()->load($customer_id);
        if (!($creditcustomer->getId())) {
            //Add credit to new customer
            $customerData = [
                'customer_id'=>$customer_id,
                'credit'=>0
            ];
            $this->_creditcustomerFactory->create()->saveCreditCustomer($customerData);
        }

            $this->messageManager->addSuccess(__("You have successfully saved affiliate account"));
            $this->_redirect('affiliate/index/referralaccount/');
    }
}
