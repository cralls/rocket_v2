<?php

namespace Meetanshi\Alipay\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\OrderNotifier;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Meetanshi\Alipay\Helper\Data as AlipayHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class Payment
 * @package Meetanshi\Alipay\Controller
 */
abstract class Payment extends Action
{
    /**
     * @var
     */
    protected $customerSession;
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var
     */
    protected $resultJsonFactory;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $alipayPayment;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var
     */
    protected $config;
    /**
     * @var
     */
    protected $logger;
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;
    /**
     * @var Builder
     */
    protected $transactionBuilder;
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var AlipayHelper
     */
    protected $helper;
    /**
     * @var CollectionFactory
     */
    protected $orderCollection;
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    /**
     * @var OrderNotifier
     */
    protected $orderSender;

    /**
     * Payment constructor.
     * @param Context $context
     * @param PaymentHelper $paymentHelper
     * @param OrderFactory $orderFactory
     * @param CheckoutSession $checkoutSession
     * @param JsonFactory $resultJsonFactory
     * @param OrderNotifier $orderSender
     * @param StoreManagerInterface $storeManager
     * @param InvoiceSender $invoiceSender
     * @param TransactionFactory $transactionFactory
     * @param Http $request
     * @param Builder $transactionBuilder
     * @param AlipayHelper $helper
     * @param InvoiceService $invoiceService
     * @param CollectionFactory $orderCollection
     * @param array $params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(Context $context, PaymentHelper $paymentHelper, OrderFactory $orderFactory, CheckoutSession $checkoutSession, JsonFactory $resultJsonFactory, OrderNotifier $orderSender, StoreManagerInterface $storeManager, InvoiceSender $invoiceSender, TransactionFactory $transactionFactory, Http $request, Builder $transactionBuilder, AlipayHelper $helper, InvoiceService $invoiceService, CollectionFactory $orderCollection, $params = [])
    {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->alipayPayment = $paymentHelper->getMethodInstance('alipay');
        $this->jsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->transactionBuilder = $transactionBuilder;
        $this->helper = $helper;
        $this->transactionFactory = $transactionFactory;
        $this->orderCollection = $orderCollection;
        $this->invoiceSender = $invoiceSender;
        $this->invoiceService = $invoiceService;
        parent::__construct($context);
    }
}
