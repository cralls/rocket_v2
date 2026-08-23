<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Model\Method;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

class Alipay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'alipay';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    protected $alipayClient;

    protected $alipayLogger;

    /**
     *
     * @param \Magento\Framework\Model\Context                             $context                [description]
     * @param \Magento\Framework\Registry                                 $registry               [description]
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory       [description]
     * @param \Magento\Framework\Api\AttributeValueFactory                 $customAttributeFactory [description]
     * @param \Magento\Payment\Helper\Data                                 $paymentData            [description]
     * @param \Magento\Framework\App\Config\ScopeConfigInterface            $scopeConfig            [description]
     * @param \Magento\Payment\Model\Method\Logger                          $logger                 [description]
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager           [description]
     * @param \Magento\Framework\UrlInterface                             $urlBuilder             [description]
     * @param \Magento\Checkout\Model\Session                              $checkoutSession        [description]
     * @param \Silksoftwarecorp\Alipay\Model\AlipayClient                  $alipayClient           [description]
     * @param \Silksoftwarecorp\Alipay\Model\Logger                        $alipayLogger           [description]
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory       $exception              [description]
     * @param \Magento\Sales\Api\TransactionRepositoryInterface            $transactionRepository  [description]
     * @param \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder     [description]
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource                                                   $resource               [description]
     * @param \Magento\Framework\Data\Collection\AbstractDb                                                   $resourceCollection     [description]
     * @param array                                                    $data                   [description]
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Silksoftwarecorp\Alipay\Model\AlipayClient $alipayClient,
        \Silksoftwarecorp\Alipay\Model\Logger $alipayLogger,
        \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_checkoutSession = $checkoutSession;
        $this->_exception = $exception;
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->alipayClient = $alipayClient;
        $this->alipayLogger = $alipayLogger;
    }


    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->alipayClient->alipayTradeRefund($payment, $amount);
        return $this;
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if($transactionId = $payment->getAdditionalInformation('trade_no')){
            if ($amount <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
            }
            $payment->setAmount($amount);

            $payment->setTransactionId($transactionId);
            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo(
                    'trade_no',
                    $transactionId
                );
            $payment->setLastTransId($transactionId);

            $order = $payment->getOrder();
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($transactionId)
                ->setFailSafe(true)
                ->build(Transaction::TYPE_CAPTURE);
        }
        return $this;
    }


}
