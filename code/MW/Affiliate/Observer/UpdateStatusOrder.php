<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateStatusOrder implements ObserverInterface
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $creditHistoryFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \MW\Affiliate\Model\CreditorderFactorty
     */
    protected $creditorderFactorty;

    /**
     * UpdateStatusOrder constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param \MW\Affiliate\Model\CredithistoryFactory $creditHistoryFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \MW\Affiliate\Model\CreditorderFactory $creditorderFactorty
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        \MW\Affiliate\Model\CredithistoryFactory $creditHistoryFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MW\Affiliate\Model\CreditorderFactory $creditorderFactorty
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_messageManager = $messageManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_urlManager = $urlManager;
        $this->_redirect = $redirect;
        $this->_dataHelper = $dataHelper;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        $this->creditHistoryFactory = $creditHistoryFactory;
        $this->orderFactory = $orderFactory;
        $this->creditorderFactorty = $creditorderFactorty;
    }

    /**
     * TODO: Re-check referral code
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $storeCode = $this->_storeManager->getStore($storeId)->getCode();
        if ($this->_dataHelper->getModelExtensions('\MW\Affiliate\Helper\Data')->moduleEnabled($storeCode)) {


            $customer_id = (int)$order->getCustomerId();
            $order_id = $order->getIncrementId();

            if ($order->getStatus() == 'complete' || $order->getStatus() == 'processing') {
                $this->saveOrderComplete($order_id, $customer_id);
            }
            if ($order->getStatus() == 'closed') {
                $this->saveOrderClosed($order_id, $customer_id);
            }

            if ($order->getStatus() == 'canceled') {
                $this->saveOrderCanceled($order, $order_id, $customer_id);
            }
        }
    }

    // update status complete cho order use credit to checkout
    public function saveOrderComplete($order_id, $customer_id)
    {
        if ($customer_id) {
            $collection = $this->creditHistoryFactory->create()
                ->getCollection()
                ->addFieldToFilter('type_transaction', \MW\Affiliate\Model\Transactiontype::USE_TO_CHECKOUT)
                ->addFieldToFilter('customer_id', $customer_id)
                ->addFieldToFilter('transaction_detail', $order_id);

            foreach ($collection as $credithistory) {
                $status = \MW\Credit\Model\Orderstatus::COMPLETE;
                $credithistory ->setStatus($status)->save();
            }
        }
    }

    // update status canceled cho order use credit to checkout
    public function saveOrderCanceled($order, $order_id, $customer_id)
    {
        if ($customer_id) {
            $creditcustomer = $this->_creditcustomerFactory->create()->load($customer_id);
            $oldcredit = $creditcustomer->getCredit();
            $collection = $this->creditHistoryFactory->create()
                ->getCollection()
                ->addFieldToFilter('type_transaction', \MW\Affiliate\Model\Transactiontype::USE_TO_CHECKOUT)
                ->addFieldToFilter('customer_id', $customer_id)
                ->addFieldToFilter('transaction_detail', $order_id)
                ->addFieldToFilter('status', \MW\Affiliate\Model\Orderstatus::PENDING);

            foreach ($collection as $credithistory) {
                // chi cap nhat lai trang thai va set lai credit
                $amount=$credithistory->getAmount();
                $newcredit = $oldcredit - $amount;
                $oldcredit = round($oldcredit, 2);
                $newcredit = round($newcredit, 2);
                $amount = round($amount, 2);
                $status = \MW\Affiliate\Model\Orderstatus::CANCELED;
                $credithistory ->setStatus($status)->save();

                // luu them vao credit history kieu cancel order using credit to checkout
                $historyData = [
                    'customer_id'            => $customer_id,
                    'type_transaction'        => \MW\Affiliate\Model\Transactiontype::CANCEL_USE_TO_CHECKOUT,
                    'status'                => MW\Affiliate\Model\Orderstatus::COMPLETE,
                    'transaction_detail'    => $order_id,
                    'amount'                => -$amount,
                    'beginning_transaction'=> $oldcredit,
                    'end_transaction'        => $newcredit,
                    'created_time'            => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                ];
                $this->creditHistoryFactory->create()->setData($historyData)->save();

                // refund credit, add again credit
                $creditcustomer->setCredit($newcredit)->save();
            }
            // gui mail cho customer khi checkout credit co don hang bi huy
            $credit = $this->creditorderFactorty->create()->load($order_id)->getCredit();
            if ($credit > 0) {
                $storeId = $order->getStoreId();
                $store_name = $this->_storeManager->getStore($storeId)->getName();
                $storeCode = $this->_storeManager->getStore($storeId)->getCode();
                $sender = $this->_dataHelper->getStoreConfig('affiliate/customer/email_sender', $storeCode);
                $email = $this->_customerFactory->create()->load($customer_id)->getEmail();
                $name = $this->_customerFactory->create()->load($customer_id)->getName();
                $teampale = 'affiliate/customer/email_template_credit_balance_changed';
                $sender_name = $this->_dataHelper->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
                $customer_credit_link = $this->_storeManager->getStore($storeId)->getUrl('credit');
                $data_mail['customer_name'] = $name;
                $data_mail['transaction_amount'] = $this->_dataHelper->formatMoney(-$amount);
                $data_mail['customer_balance'] = $this->_dataHelper->formatMoney($newcredit);
                $comment = __('You checkout by credit order #%s. Your order was cancelled', $order_id);
                $data_mail['transaction_detail'] = $comment;
                $data_mail['transaction_time'] = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                $data_mail['sender_name'] = $sender_name;
                $data_mail['store_name'] = $store_name;
                $data_mail['customer_credit_link'] = $customer_credit_link;
                $this->_dataHelper->getModelExtensions('\MW\Affiliate\Helper\Data')->_sendEmailTransactionNew($sender, $email, $name, $teampale, $data_mail, $storeCode);
            }
        }
    }

    //bat su kien refund order khi su dung credit de check out
    public function saveOrderClosed($order_id, $customer_id)
    {
        $collection = $this->creditHistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('type_transaction', \MW\Affiliate\Model\Transactiontype::USE_TO_CHECKOUT)
            ->addFieldToFilter('customer_id', $customer_id)
            ->addFieldToFilter('transaction_detail', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Orderstatus::COMPLETE);

        foreach ($collection as $credithistory) {
            $status = \MW\Affiliate\Model\Orderstatus::CLOSED;
            $credithistory ->setStatus($status)->save();
        }
    }
}
