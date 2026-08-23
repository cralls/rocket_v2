<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MW\Affiliate\Observer\Cronjob;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class Schedule implements ObserverInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MW\Affiliate\Helper\Data $dataHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_messageManager = $messageManager;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** not to do anything  */
    }


    /**  set total member of programs */
    public function runCronMemberProgram()
    {
        $this->_dataHelper->setTotalMemberProgram();
    }

    /** release holding commission */
    public function runCronHoldingCommission()
    {

        $collections = $this->_dataHelper->getModel('Affiliatehistory')
            ->getCollection()
            ->addFieldToFilter('status', ['eq'=>\MW\Affiliate\Model\Status::HOLDING]);

        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        /*
        $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Stdlib\DateTime\DateTime'
        );
        $currentTime = $dateTime->timestamp();
        */
        //$currentTime = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());

        $currentTime = (new \DateTime())->getTimestamp();

        $affiliateCustomerModel = $this->_dataHelper->getModel('Affiliatecustomers');
        $creditHistoryModel = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Credithistory');
        $creditCustomerModel = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Creditcustomer');

        foreach ($collections as $item) {
            $beginningTime = strtotime($item->getTransactionTime());
            $holdingTime = intval($this->_dataHelper->getStoreConfig('affiliate/general/commission_holding_period')) * 86400;

            if ($beginningTime + $holdingTime < $currentTime) {
                /* Update affiliate customer table */
                $affiliateCustomer = $affiliateCustomerModel->load($item->getCustomerInvited());
                $currentCommission = $affiliateCustomer->getTotalCommission();
                $affiliateCustomer->setTotalCommission($currentCommission + $item->getHistoryCommission());
                $affiliateCustomer->save();

                /* Update credit history table */
                $creditHistory = $creditHistoryModel
                    ->getCollection()
                    ->addFieldToFilter('transaction_detail', ['eq' => $item->getOrderId()])
                    ->getFirstItem();
                $creditHistoryModel->load($creditHistory->getCreditHistoryId())->setStatus(\MW\Affiliate\Model\Orderstatus::COMPLETE);
                $creditHistoryModel->save();

                /* Update credit customer table */
                $creditCustomer = $creditCustomerModel->load($item->getCustomerInvited());
                $currentCredit = $creditCustomer->getCredit();
                $creditCustomer->setCredit($currentCredit + $item->getHistoryCommission());
                $creditCustomer->save();

                /* Update affiliate history table */
                $item->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
                $item->setStatus(\MW\Affiliate\Model\Status::COMPLETE);
                $item->save();
                $transaction_collections = $this->_dataHelper->getModel('Affiliatetransaction')->getCollection()
                    ->addFieldToFilter('order_id', $item->getOrderId())
                    ->addFieldToFilter('status', ['eq' => \MW\Affiliate\Model\Status::HOLDING]);
                foreach ($transaction_collections as $transaction_collection) {
                    $transaction_collection->setStatus(\MW\Affiliate\Model\Status::COMPLETE)->save();
                }

            }
        }
    }

    /** Auto create withDraw request */
    public function runCron()
    {
        $storeCode = $this->_storeManager->getStore()->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            /** @var  $day_week || day of week */
            $day_week = (int)date('w', (new \DateTime())->getTimestamp());
            /** @var  $day_month || day of month  */
            $day_month = (int)date('j', (new \DateTime())->getTimestamp());

            $withdrawn_period = (int)$this->_dataHelper->getWithdrawnPeriodStore($storeCode);
            $withdrawn_days = (int)$this->_dataHelper->getWithdrawnDayStore($storeCode);
            $withdrawn_month = (int)$this->_dataHelper->getWithdrawnMonthStore($storeCode);
            if (is_null($withdrawn_days)) {
                $withdrawn_days = 50;
            }
            if (is_null($withdrawn_month)) {
                $withdrawn_month = 50;
            }
            $fee = (int)$this->_dataHelper->getFeeStore($storeCode);
            if (($withdrawn_period == 1 && $day_week == $withdrawn_days) || ($withdrawn_period == 2 && $day_month == $withdrawn_month)) {
                $collections = $this->_dataHelper->getModel('Affiliatecustomers')
                    ->getCollection()
                    ->addFieldToFilter('active', \MW\Affiliate\Model\Statusactive::ACTIVE)
                    ->addFieldToFilter('auto_withdrawn', \MW\Affiliate\Model\Autowithdrawn::AUTO)
                    ->addFieldToFilter('status', \MW\Affiliate\Model\Statusreferral::ENABLED);
                foreach ($collections as $affiliate_customer) {
                    /* auto withdraw if reachs this value */
                    $withdrawn_level = $affiliate_customer ->getWithdrawnLevel();
                    $customer_id = $affiliate_customer ->getCustomerId();
                    /* keep in account */
                    $reserve_level = $affiliate_customer ->getReserveLevel();
                    $withdrawn = $withdrawn_level + $reserve_level;

                    $creditcustomer = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Creditcustomer')->load($customer_id);
                    $credit = $creditcustomer->getCredit();
                    if ($credit >= $withdrawn) {
                        // save in withdrawn table
                        $withdraw_receive = $credit - $fee;
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
                            'status'            => \MW\Affiliate\Model\Status::PENDING,
                            'withdrawn_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                        ];
                        $this->_dataHelper->getModel('Affiliatewithdrawn')->setData($withdrawnData)->save();

                        // update lai credit
                        $oldCredit = $creditcustomer->getCredit();
                        $amount = - $credit;
                        $newCredit = $oldCredit + $amount;
                        $newCredit = round($newCredit, 2);
                        $creditcustomer->setCredit($newCredit)->save();


                        $collectionWithdrawn = $this->_dataHelper->getModel('Affiliatewithdrawn')
                            ->getCollection()
                            ->addFieldToFilter('customer_id', $customer_id)
                            ->setOrder('withdrawn_id', 'DESC')
                            ->getFirstItems();
                        $withdrawn_id = $collectionWithdrawn->getWithdrawnId();


                        // luu vao bang credit history
                        $historyData = [
                            'customer_id'            => $customer_id,
                            'type_transaction'        => \MW\Affiliate\Model\Transactiontype::WITHDRAWN,
                            'status'                => \MW\Affiliate\Model\Orderstatus::PENDING,
                            'transaction_detail'    => $withdrawn_id,
                            'amount'                => $amount,
                            'beginning_transaction'=> $oldCredit,
                            'end_transaction'        => $newCredit,
                            'created_time'            => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                        ];
                        $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Credithistory')->setData($historyData)->save();

                        // gui mail cho khach hang khi rut tien tu dong
                        $this->_dataHelper->sendMailCustomerRequestWithdrawn($customer_id, $credit, $storeCode);
                    }
                }
            }
        }
    }
}
