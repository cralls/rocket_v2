<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditSaveOrderAfter implements ObserverInterface
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
     * CreditSaveOrderAfter constructor.
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
        \Magento\Sales\Model\OrderFactory $orderFactory
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
            $customerId = (int)$order->getCustomerId();
            if ($customerId) {
                //Subtract credit points of customer and save to order if customer use this credit to checkout Mage::getSingleton('checkout/session')
                $amountCredit = $this->_dataHelper->_Session()->getCredit();
                if ($amountCredit > 0) {
                    $creditcustomer = $this->_creditcustomerFactory->create()->load($customerId);
                    $oldCredit = $creditcustomer->getCredit();
                    $newCredit = $oldCredit - $amountCredit;
                    $newCredit = round($newCredit, 2);
                    $amountCredit = round($amountCredit, 2);
                    $oldCredit = round($oldCredit, 2);

                    // Save history transaction
                    $historyData = [
                        'customer_id'            => $customerId,
                        'type_transaction'        => \MW\Affiliate\Model\Transactiontype::USE_TO_CHECKOUT,
                        'transaction_detail'    => $order->getIncrementId(),
                        'amount'                => -$amountCredit,
                        'beginning_transaction'=> $oldCredit,
                        'end_transaction'        => $newCredit,
                        'status'                => \MW\Affiliate\Model\Orderstatus::PENDING,
                        'created_time'            => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                    ];
                    $this->creditHistoryFactory->create()->setData($historyData)->save();

                    //Subtract credit of customer
                    $creditcustomer->setCredit($newCredit)->save();

                    // gui mail cho khach hang khi dung credit de check out
                    $store_name = $this->_storeManager->getStore($storeId)->getName();
                    $storeCode = $this->_storeManager->getStore($storeId)->getCode();
                    $sender = $this->_dataHelper->getStoreConfig('affiliate/customer/email_sender', $storeCode);
                    $email = $this->_customerFactory->create()->load($customerId)->getEmail();
                    $name = $this->_customerFactory->create()->load($customerId)->getName();
                    $teampale = 'affiliate/customer/email_template_credit_balance_changed';
                    $sender_name = $this->_dataHelper->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
                    $customer_credit_link = $this->_storeManager->getStore($storeId)->getUrl('credit');
                    $data_mail['customer_name'] = $name;
                    $data_mail['transaction_amount'] = $this->_dataHelper->formatMoney(-$amountCredit);
                    $data_mail['customer_balance'] = $this->_dataHelper->formatMoney($newCredit);
                    $comment = __('You checkout by credit order #%s', $order->getIncrementId());
                    $data_mail['transaction_detail'] = $comment;
                    $data_mail['transaction_time'] = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
                    $data_mail['sender_name'] = $sender_name;
                    $data_mail['store_name'] = $store_name;
                    $data_mail['customer_credit_link'] = $customer_credit_link;
                    $this->_dataHelper->getModelExtensions('\MW\Affiliate\Helper\Data')->_sendEmailTransactionNew($sender, $email, $name, $teampale, $data_mail, $storeCode);
                }
                //Reset credit in Session
                $this->_dataHelper->_Session()->unsetData('credit');
                $this->_dataHelper->_Session()->setCredit(0);
            }
        }
    }
}
