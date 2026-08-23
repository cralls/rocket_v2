<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Model\Method;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi;
use Silksoftwarecorp\WechatPay\Model\Api\Data\Refund as DataRefund;

class WechatPay extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'wechat_pay';
    const NOTIFY_URL_PATH = 'rest/V1/wechatpay';
    const PAY_URL_PATH = 'wechatpay/checkout/pay';
    const CONFIRM_URL_PATH = 'wechatpay/checkout/confirm';

    protected $_code = self::METHOD_CODE;

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
    protected $_isInitializeNeeded = true;

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

    protected $nativePay;

    protected $jsApiPay;

    protected $helper;

    protected $apiLogger;

    protected $apiConfig;

    /**
     * @param \Magento\Framework\Model\Context                              $context
     * @param \Magento\Framework\Registry                                   $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory             $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory                  $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                                  $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface            $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                          $logger
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManager
     * @param \Magento\Framework\UrlInterface                               $urlBuilder
     * @param \Magento\Checkout\Model\Session                               $checkoutSession
     * @param \Silksoftwarecorp\WechatPay\Model\NativePay                   $nativePay
     * @param \Silksoftwarecorp\WechatPay\Model\JsApiPay                    $jsApiPay
     * @param \Silksoftwarecorp\WechatPay\Model\Api\Config                  $apiConfig
     * @param \Silksoftwarecorp\WechatPay\Helper\Data                       $helper
     * @param \Silksoftwarecorp\WechatPay\Model\Logger                      $apiLogger
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory        $exception
     * @param \Magento\Sales\Api\TransactionRepositoryInterface             $transactionRepository
     * @param \Magento\Sales\Model\Order\Payment\TransactionBuilderInterface $transactionBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null  $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null            $resourceCollection
     * @param array                                                         $data
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
        \Silksoftwarecorp\WechatPay\Model\NativePay $nativePay,
        \Silksoftwarecorp\WechatPay\Model\JsApiPay $jsApiPay,
        \Silksoftwarecorp\WechatPay\Model\Api\Config $apiConfig,
        \Silksoftwarecorp\WechatPay\Helper\Data $helper,
        \Silksoftwarecorp\WechatPay\Model\Logger $apiLogger,
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
        $this->nativePay = $nativePay;
        $this->jsApiPay = $jsApiPay;
        $this->helper = $helper;
        $this->apiLogger = $apiLogger;
        $this->apiConfig = $apiConfig;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        try{
            $orderQueryInput = new \Silksoftwarecorp\WechatPay\Model\Api\Data\OrderQuery;
            $orderQueryInput->SetOut_trade_no("Magento_Check_API_Validate");
            $result = WechatPayApi::orderQuery($this->apiConfig, $orderQueryInput);
            if(isset($result['return_code']) && $result['return_code'] == "FAIL"){
                throw new \Exception(__("There is something error."));
            }
        }catch(\Exception $e){
            throw new \Exception(__("There is something error."));
        }

        $stateObject->setState("new");
        $stateObject->setStatus('pending');
        $stateObject->setIsNotified(false);

    }

    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The order action is not available.'));
        }

        try{
            $orderQueryInput = new \Silksoftwarecorp\WechatPay\Model\Api\Data\OrderQuery;
            $orderQueryInput->SetOut_trade_no("Magento_Check_API_Validate");
            $result = WechatPayApi::orderQuery($this->apiConfig, $orderQueryInput);
            if(isset($result['return_code']) && $result['return_code'] == "FAIL"){
                throw new \Exception(__("There is something error."));
            }
        }catch(\Exception $e){
            throw new \Exception(__("There is something error."));
        }

        return $this;
    }

    public function getPayPaymentInfo(\Magento\Sales\Model\Order $order){
        $input = new \Silksoftwarecorp\WechatPay\Model\Api\Data\UnifiedOrder;
        $input->SetBody($order->getIncrementId());
        $input->SetOut_trade_no($order->getIncrementId());
        $totalFee = $this->helper->formatTotal($order->getGrandTotal(), $order->getBaseCurrencyCode());

        $input->SetTotal_fee($totalFee);
        $input->SetFee_type($order->getBaseCurrencyCode());
        $input->SetTime_start($this->helper->formatTime("YmdHis"));
        $input->SetTime_expire($this->helper->formatTime("YmdHis", 600));
        //$input->SetGoods_tag("test");

        $payPaymentInfo = array(
            'trade_type'    => '',
            'code_url'      => '',
            'js_api_parameters' => [],
            'edit_address'  => [],
            'mweb_url' => ''
        );
        try{
            if($this->helper->isInWechat()){
                $payment = $order->getPayment();
                if(!$payment->getAdditionalInformation('open_id')){
                    $openId = $this->jsApiPay->getOpenid();
                    if(!$openId){
                        return $payPaymentInfo;
                    }
                    $payment->setAdditionalInformation('open_id', $openId);
                    $payment->save();
                }else{
                    $openId = $payment->getAdditionalInformation('open_id');
                }

                $input->SetTrade_type("JSAPI");
    	        $input->SetOpenid($openId);
                $orderData = WechatPayApi::unifiedOrder($this->apiConfig, $input);
                $jsApiParameters = $this->jsApiPay->GetJsApiParameters($orderData);

    	        //获取共享收货地址js函数参数
    	        $editAddress = $this->jsApiPay->GetEditAddressParameters();

                $payPaymentInfo['trade_type'] = 'JSAPI';
                $payPaymentInfo['js_api_parameters'] = $jsApiParameters;
                $payPaymentInfo['edit_address'] = $editAddress;

            }elseif($this->helper->isMobile()){
                $input->SetTrade_type("MWEB");
                $sceneInfoJson = '{"h5_info": {"type":"Wap","wap_url": "'.$this->_storeManager->getStore()->getBaseUrl().'","wap_name": "'.$this->_storeManager->getStore()->getName().'"}}';
                $input->SetSceneInfo($sceneInfoJson);
                $result = WechatPayApi::unifiedOrder($this->apiConfig, $input);
                if(isset($result['mweb_url'])){
                    $successUrl = urlencode($this->helper->getUrl(self::CONFIRM_URL_PATH, array('id'=>$order->getId())));
                    $mwebUrl = $result["mweb_url"].'&redirect_url='.$successUrl;
                    $payPaymentInfo['trade_type'] = 'MWEB';
                    $payPaymentInfo['mweb_url'] = $mwebUrl;
                }
            }else{
                $input->SetTrade_type("NATIVE");
                $input->SetProduct_id($this->helper->getPaymentConfig('merchant_id').'-'.$order->getId());
                $result = $this->nativePay->getPayUrl($input);
                if(isset($result['code_url'])){
                    $codeUrl = $result["code_url"];
                    $payPaymentInfo['trade_type'] = 'NATIVE';
                    $payPaymentInfo['code_url'] = $codeUrl;
                }
            }
        }catch(\Exception $e){
	           $this->apiLogger->error($e->getMessage());
        }


        return $payPaymentInfo;

    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
	    $order = $payment->getOrder();
        $creditmemo = $payment->getCreditmemo();
        $transcationId = $payment->getAdditionalInformation('transaction_id');
        $totalFee = $order->getBaseGrandTotal();
        $refundFee = $amount;
        if(!$refundFee){
            $refundFee = $totalFee;
        }
        $input = new DataRefund();
        $input->SetTransaction_id($transcationId);
	    $input->SetTotal_fee($this->helper->formatTotal($totalFee, $order->getBaseCurrencyCode()));
	    $input->SetRefund_fee($this->helper->formatTotal($refundFee, $order->getBaseCurrencyCode()));
        $input->SetRefund_fee_type($order->getBaseCurrencyCode());
        $input->SetOut_refund_no('CM'.$creditmemo->getInvoiceId().date('YmdHis'));
	    $input->SetOp_user_id($this->apiConfig->GetMerchantId());
        WechatPayApi::refund($this->apiConfig, $input);
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
        if($transactionId = $payment->getAdditionalInformation('transaction_id')){
            if ($amount <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for capture.'));
            }
            $payment->setAmount($amount);

            $payment->setTransactionId($transactionId);
            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo(
                    'transaction_id',
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
