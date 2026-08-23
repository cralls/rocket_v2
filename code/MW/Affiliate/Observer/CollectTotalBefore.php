<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;

class CollectTotalBefore implements ObserverInterface
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
     * CollectTotalBefore constructor.
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
        $storeCode = $this->_storeManager->getStore()->getCode();
        if ($this->_dataHelper->getModelExtensions('\MW\Affiliate\Helper\Data')->moduleEnabled($storeCode)) {
            $credit  = (int)$this->_getSession()->getCredit();
            $quote = $observer->getEvent()->getQuote();

            $address = $quote->isVirtual()?$quote->getBillingAddress():$quote->getShippingAddress();
            $subtotal = $address->getBaseSubtotal();
            $subtotal += $address->getBaseDiscountAmount() + $credit;

            if ($credit > $subtotal) {
                $this->_getSession()->setCredit(0);
            }
        }
    }


    private function _getSession()
    {
        return $this->_dataHelper->_Session();
    }
}
