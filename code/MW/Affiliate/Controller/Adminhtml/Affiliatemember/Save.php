<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statuswebsite;
use MW\Affiliate\Model\Autowithdrawn;
use MW\Affiliate\Model\Transactiontype;
use MW\Affiliate\Model\Orderstatus;

class Save extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    public function execute()
    {
        if ($this->getRequest()->getParam('customer_id')) {
            $this->editAffiliate();
        } else {
            $this->addNewAffiliate();
        }
    }

    /**
     * Add New Active Affiliate Member
     */
    public function addNewAffiliate()
    {
        $data = $this->getRequest()->getParams();

        if ($data) {
            $customerId = $this->_customerFactory->create()
                ->getCollection()
                ->addFieldToFilter('email', ['eq' => $data['customer_email']])
                ->getFirstItem()
                ->getId();

            // Check if customer is available or not
            if ($customerId) {
                // Save affiliate customer to database
                $affiliateCustomerModel = $this->_affiliatecustomersFactory->create();
                $customerData = [
                    'customer_id'        => $customerId,
                    'active'            => Statusactive::ACTIVE,
                    'payment_gateway'    => $data['payment_gateway'],
                    'payment_email'        => $data['payment_email'],
                    'auto_withdrawn'    => $data['auto_withdrawn'],
                    'withdrawn_level'    => $data['withdrawn_level'],
                    'reserve_level'        => $data['reserve_level'],
                    'bank_name'            => $data['bank_name'],
                    'name_account'        => $data['name_account'],
                    'bank_country'        => $data['bank_country'],
                    'swift_bic'            => $data['swift_bic'],
                    'account_number'    => $data['account_number'],
                    're_account_number'    => $data['re_account_number'],
                    'total_commission'    => 0,
                    'total_paid'        => 0,
                    'referral_code'        => '',
                    'status'            => $data['affiliate_status'],
                    'invitation_type'    => Typeinvitation::NON_REFERRAL,
                    'customer_time'     => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                    'customer_invited'    => 0
                ];

                try {
                    $affiliateCustomerModel->setData($customerData)->save();

                    $creditcustomer = $this->_creditcustomerFactory->create()->load($customerId);
                    if (!($creditcustomer->getId())) {
                        //Add credit to new customer
                        $customerData = [
                            'customer_id' => $customerId,
                            'credit' => 0
                        ];
                        $this->_creditcustomerFactory->create()->saveCreditCustomer($customerData);
                    }

                    // Set Referral Code
                    $this->_dataHelper->setReferralCode($affiliateCustomerModel->getId());

                    // Save affiliate websites to database
                    $referralSite = $data['referral_site'];
                    if ($referralSite) {
                        $sites = explode(',', $referralSite);
                        foreach ($sites as $url) {
                            $websiteModel = $this->_websitememberFactory->create();
                            $siteItem = [
                                'customer_id'   => $customerId,
                                'domain_name'    => trim($url),
                                'verified_key'  => $this->_dataHelper->getWebsiteVerificationKey(trim($url)),
                                'status'        => Statuswebsite::UNVERIFIED
                            ];
                            $websiteModel->setData($siteItem)->save();
                        }
                    }

                    // Set group for affiliate
                    $group = [
                        'customer_id' => $customerId,
                        'group_id'    => $data['group_id']
                    ];
                    $this->_groupmemberFactory->create()->setData($group)->save();

                    // Send email to affilite to notify success
                    $this->_dataHelper->sendMailCustomerActiveAffiliate($customerId);

                    $this->messageManager->addSuccess(__('The member has successfully added'));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                $this->messageManager->addError(__('The customer does not exist'));
            }
        } else {
            $this->messageManager->addError(__('Unable to save new affiliate'));
        }

        $this->_redirect('*/*/');
    }

    /**
     * Save Active Affiliate Member
     */
    public function editAffiliate()
    {
        if ($this->getRequest()->getPost()) {
            $data = $this->getRequest()->getParams();
            $memberId = $this->getRequest()->getParam('customer_id');
            $model = $this->_affiliatecustomersFactory->create()->load($memberId);

            try {
                $customer = $this->_customerFactory->create()->load($memberId);
                $store = $this->_storeFactory->create()->load($customer->getStoreId());
                $max = (double) $this->_dataHelper->getWithdrawMaxStore($store->getCode());
                $min = (double) $this->_dataHelper->getWithdrawMinStore($store->getCode());

                if (isset($data['affiliate_parent']) && $data['affiliate_parent'] != '') {
                    $parentAffliate = $this->_customerFactory->create()->getCollection()
                        ->addFieldToFilter('email', $data['affiliate_parent'])
                        ->getFirstItem();

                    if ($parentAffliate) {
                        $checkAvailable = $this->_dataHelper->getActiveAndEnableAffiliate($parentAffliate->getId());

                        if ($parentAffliate->getId() != $memberId && $checkAvailable == 1) {
                            $data['customer_invited'] = $parentAffliate->getId();
                        } else {
                            $this->messageManager->addError(__('Affiliate parent invalid'));
                            $this->_redirect('*/*/edit', ['id' => $memberId]);
                            return;
                        }
                    } else {
                        $this->messageManager->addError(__('Affiliate parent invalid'));
                        $this->_redirect('*/*/edit', ['id' => $memberId]);
                        return;
                    }
                } else {
                    $data['customer_invited'] = 0;
                }

                if ($data['payment_gateway'] != 'banktransfer' && $data['payment_gateway'] != 'check') {
                    $affiliate = $this->_affiliatecustomersFactory->create()->getCollection()
                        ->addFieldToFilter('payment_email', ['eq' => $data['payment_email']])
                        ->addFieldToFilter('customer_id', ['neq' => $memberId]);

                    //if (sizeof($affiliate) > 0) {
                        //$this->messageManager->addError(__('There is already an account with this emails paypal'));
                        //$this->_redirect('*/*/edit', ['id' => $memberId]);
                        //return;
                    //}
                } else {
                    $data['payment_email'] = '';
                }

                $withdrawnLevel = (double) $data['withdrawn_level'];
                $autoWithdrawn = (int) $data['auto_withdrawn'];
                if ($autoWithdrawn == Autowithdrawn::AUTO) {
                    if ($withdrawnLevel < $min || $withdrawnLevel > $max) {
                        $this->messageManager->addError(__(
                            'Please insert a value of Auto payment when account balance reaches that is in range of [%1, %2]',
                            $min,
                            $max
                        ));
                        $this->_redirect('*/*/edit', ['id' => $memberId]);
                        return;
                    }
                }

                if (isset($data['reserve_level']) && !$data['reserve_level']) {
                    $data['reserve_level'] = 0;
                }

                if (isset($data['referral_site']) && !$data['referral_site']) {
                    $data['referral_site'] = '';
                }

                if (isset($data['payment_gateway']) && $data['payment_gateway'] != 'banktransfer') {
                    $data['bank_name'] = '';
                    $data['name_account'] = '';
                    $data['bank_country'] = '';
                    $data['swift_bic'] = '';
                    $data['account_number'] = '';
                    $data['re_account_number'] = '';
                }

                if (isset($data['affiliate_status'])) {
                    $data['status'] = $data['affiliate_status'];
                    unset($data['affiliate_status']);
                }

                // Save affiliate
                $model->setData($data)->save();

                // Add affiliate member to group
                if ($data['group_id'] != 0) {
                    // Remove old members data of group to update new members data
                    $groupMember = $this->_groupmemberFactory->create()->load($memberId, 'customer_id');
                    if ($groupMember) {
                        $groupMember->delete();
                    }

                    $groupMemberData = [
                        'group_id' => $data['group_id'],
                        'customer_id' => $memberId
                    ];
                    $this->_groupmemberFactory->create()->setData($groupMemberData)->save();
                }

                // Set total member customer program
                $this->_dataHelper->setTotalMemberProgram();

                // Edit customer
                // In case add or remove credit by admin with causes
                $amountCredit = $data['amount_credit'];
                if ($amountCredit != 0) {
                    // Update affiliate customer table
                    $affiliateCustomerModel = $this->_affiliatecustomersFactory->create()->load($memberId);
                    $oldCommission = $affiliateCustomerModel->getTotalCommission();
                    $newCommission = $oldCommission + $amountCredit;
                    $newCommission = round($newCommission, 2);
                    $affiliateCustomerModel->setTotalCommission($newCommission)->save();

                    // Update credit customer table
                    $creditcustomer = $this->_creditcustomerFactory->create()->load($memberId);
                    $oldCredit = $creditcustomer->getCredit();
                    $comment = '';
                    if ($data['comment']) {
                        $comment = $data['comment'];
                    }
                    $newCredit = $oldCredit + $amountCredit;
                    $newCredit = round($newCredit, 2);
                    $amountCredit = round($amountCredit, 2);
                    $oldCredit = round($oldCredit, 2);
                    $creditcustomer->setCredit($newCredit)->save();

                    // Save history transaction for customer
                    $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                    $historyData = [
                        'customer_id'             => $memberId,
                        'type_transaction'        => Transactiontype::ADMIN_CHANGE,
                        'transaction_detail'    => $comment,
                        'amount'                => $amountCredit,
                        'beginning_transaction'    => $oldCredit,
                        'end_transaction'        => $newCredit,
                        'status'                => Orderstatus::COMPLETE,
                        'created_time'            => $now
                    ];

                    $this->_credithistoryFactory->create()->setData($historyData)->save();

                    // Send notification email to customer when admin add or substract credit with comment
                    $emailData = [
                        'transaction_amount' => $amountCredit,
                        'customer_balance' => $newCredit,
                        'transaction_detail' => $comment,
                        'transaction_time' => $now
                    ];
                    $this->_dataHelper->sendMailWhenCreditBalanceChanged($memberId, $emailData);
                }

                // In case payout for customer
                $payoutCredit = $data['payout_credit'];
                if (isset($payoutCredit) && $payoutCredit != '') {
                    $payoutCredit = (double) $payoutCredit;
                    $creditcustomer = $this->_creditcustomerFactory->create()->load($memberId);
                    $oldCredit = (double) $creditcustomer->getCredit();

                    if ($payoutCredit > 0 && $payoutCredit <= $oldCredit) {
                        $this->getRequestWithdrawComplete($memberId, $payoutCredit);
                    } else {
                        $this->messageManager->addError(__(
                            'Please insert a value of Payout that is in range of (%1, %2]',
                            0,
                            $oldCredit
                        ));
                        $this->_redirect('*/*/edit', ['id' => $memberId]);
                        return;
                    }
                }

                $this->messageManager->addSuccess(__('The member has successfully saved'));
                $this->_session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $memberId]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFormData($data);
                $this->_redirect('*/*/edit', ['id' => $memberId]);
                return;
            }
        }

        $this->messageManager->addError(__('Unable to find member to save'));
        $this->_redirect('*/*/');
    }


    public function getRequestWithdrawComplete($customer_id, $credit)
    {
        $store_id = $this->_customerFactory->create()->load($customer_id)->getStoreId();
        $store = $this->_storeFactory->create()->load($store_id);
        $gateway = $this->_affiliatecustomersFactory->create()->load($customer_id)->getPaymentGateway();
        $fee = $this->_dataHelper->getFeePaymentGateway($gateway, $store->getCode());
        $pos = strpos($fee, '%');
        if ($pos != false) {
            $percent = doubleval(substr($fee, 0, strpos($fee, '%')));
            $fee = ($percent * $credit)/100;

        } else {
            $fee = doubleval($fee);
        }
        $withdraw_receive = $credit - $fee;

        $affiliate_customer =  $this->_affiliatecustomersFactory->create()->load($customer_id);
        $payment_gateway = $affiliate_customer->getPaymentGateway();
        $payment_email = $affiliate_customer->getPaymentEmail();
        if ($payment_gateway == 'banktransfer') {
            $payment_email = '';
        }
        $bank_name = $affiliate_customer->getBankName();
        $name_account = $affiliate_customer->getNameAccount();
        $bank_country = $affiliate_customer->getBankCountry();
        $swift_bic = $affiliate_customer->getSwiftBic();
        $account_number= $affiliate_customer->getAccountNumber();
        $re_account_number = $affiliate_customer->getReAccountNumber();

        $withdrawnData =  [
            'customer_id'        => $customer_id,
            'payment_gateway'    => $payment_gateway,
            'payment_email'        => $payment_email,
            'bank_name'            => $bank_name,
            'name_account'        => $name_account,
            'bank_country'        => $bank_country,
            'swift_bic'            => $swift_bic,
            'account_number'    => $account_number,
            're_account_number'    => $re_account_number,
            'withdrawn_amount'    => $credit,
            'fee'                => $fee,
            'amount_receive'    => $withdraw_receive,
            'status'            => \MW\Affiliate\Model\Status::COMPLETE,
            'withdrawn_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())

        ];
        $this->_dataHelper->getModel('Affiliatewithdrawn')->setData($withdrawnData)->save();

        // update lai credit
        $creditcustomer = $this->_creditcustomerFactory->create()->load($customer_id);
        $oldCredit = $creditcustomer->getCredit();
        $amount = - $credit;
        $newCredit = $oldCredit + $amount;
        $newCredit=round($newCredit, 2);
        $creditcustomer->setCredit($newCredit)->save();

        $collectionWithdrawns = $this->_withdrawnFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer_id)
            ->setOrder('withdrawn_id', 'DESC');
        foreach ($collectionWithdrawns as $collectionWithdrawn) {
            $withdrawn_id = $collectionWithdrawn->getWithdrawnId();
            break;
        }

        // luu vao bang credit history
        $historyData = [
            'customer_id'            => $customer_id,
            'type_transaction'        => \MW\Affiliate\Model\Transactiontype::WITHDRAWN,
            'status'                => \MW\Affiliate\Model\Orderstatus::COMPLETE,
            'transaction_detail'    => $withdrawn_id,
            'amount'                => $amount,
            'beginning_transaction'=> $oldCredit,
            'end_transaction'        => $newCredit,
            'created_time'            => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
        ];
        $this->_credithistoryFactory->create()->setData($historyData)->save();

        // gui mail cho khach hang khi rut tien thanh cong, do admin rut
        $this->_dataHelper->sendMailCustomerWithdrawnComplete($customer_id, $amount, $store->getCode());
    }
}
