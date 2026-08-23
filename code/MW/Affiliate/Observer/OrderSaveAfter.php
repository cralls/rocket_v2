<?php


namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

class OrderSaveAfter implements ObserverInterface
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
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * SalesOrderAfter constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
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
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
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
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->orderFactory = $orderFactory;
    }


    /**
     * @param \Magento\Framework\Event\Observer $argv
     */
    public function execute(\Magento\Framework\Event\Observer $argv)
    {
        $process = $this->_dataHelper->getModelExtensions('MW\Affiliate\Service\OrderService');
        $order = $argv->getEvent()->getOrder();
        $store_id = $order->getStoreId();
        $storeCode = $this->_storeManager->getStore($store_id)->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            $status_add_commsion = $this->_dataHelper->getStatusAddCommissionStore($storeCode);
            $status_subtract_commsion = $this->_dataHelper->getStatusSubtractCommissionStore($store_id);

            $order_id = $order->getIncrementId();

            if ($order->getStatus() == $status_add_commsion) {
                $process->saveOrderComplete($order_id, $storeCode);
            }

            if ($order->getStatus() == $status_subtract_commsion) {
                $process->saveOrderClosed($order_id, $storeCode);
            }

            if ($order->getStatus() == 'canceled') {
                $process->saveOrderCanceled($order_id, $storeCode);
            }

        }
    }
}
