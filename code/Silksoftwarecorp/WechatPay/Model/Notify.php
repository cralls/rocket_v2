<?php


namespace Silksoftwarecorp\WechatPay\Model;


use Silksoftwarecorp\WechatPay\Model\Api\Data\NotifyResults;

class Notify implements \Silksoftwarecorp\WechatPay\Api\NotifyInterface
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceOrderInterface
     */
    protected $_invoiceOrder;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Api\Config
     */
    protected $apiConfig;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Api\Data\Notify
     */
    protected $notify;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\WechatPayApi
     */
    protected $api;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Logger
     */
    protected $logger;

    /**
     * @var \Silksoftwarecorp\WechatPay\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context                 $context
     * @param \Magento\Checkout\Model\Session                       $checkoutSession
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Magento\Sales\Model\OrderFactory                     $orderFactory
     * @param \Magento\Sales\Api\InvoiceOrderInterface              $invoiceOrder
     * @param \Magento\Framework\View\ResultPageFactory             $resultPageFactory
     * @param \Magento\Framework\View\ResultLayoutFactory           $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory       $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory      $resultJsonFactory
     * @param \Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi    $api
     * @param \Silksoftwarecorp\WechatPay\Model\Api\Config          $apiConfig
     * @param \Silksoftwarecorp\WechatPay\Model\Api\Data\Notify     $notify
     * @param \Silksoftwarecorp\WechatPay\Model\Logger              $logger
     * @param \Silksoftwarecorp\WechatPay\Helper\Data               $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\InvoiceOrderInterface $invoiceOrder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Silksoftwarecorp\WechatPay\Model\Api\WechatPayApi $api,
        \Silksoftwarecorp\WechatPay\Model\Api\Config $apiConfig,
        \Silksoftwarecorp\WechatPay\Model\Api\Data\Notify $notify,
        \Silksoftwarecorp\WechatPay\Model\Logger $logger,
        \Silksoftwarecorp\WechatPay\Helper\Data $helper
    ) {
        //parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_invoiceOrder = $invoiceOrder;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->api = $api;
        $this->helper = $helper;
        $this->apiConfig = $apiConfig;
        $this->notify = $notify;
    }

    /**
     * invoice order by order increment_id
     * @param  string $incrementId
     */
    protected function invoiceOrderByIncrementId($incrementId){
        $order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        if($order){
                $invoiceId = $this->_invoiceOrder->execute($order->getId(), true);

        }
    }


    /**
     * {@inheritdoc}
     */
    public function notify()
    {

        $resultXml = $this->notify->Handle($this->apiConfig, false);
        $resultRaw = $this->resultRawFactory->create();
        if($this->notify->GetReturn_code() == "SUCCESS"){
            $xml = file_get_contents("php://input");
            $result = NotifyResults::Init($this->apiConfig, $xml);
            if($result){
                $data = $result->GetValues();

                $outTradeNo = $data['out_trade_no'];
                $transactionId = $data['transaction_id'];
                $appId = $data['appid'];
                $merchantId = $data['mch_id'];
                $totalFee = $data['total_fee'];

                if($appId != $this->apiConfig->GetAppId()){ //validate app id
                    $this->logger->error("app_id doesn't match.");
                    $this->logger->error(json_encode($data));
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("APPID does not match.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

                if($merchantId != $this->apiConfig->GetMerchantId()){ //validate app id
                    $this->logger->error("Merchant ID doesn't match.");
                    $this->logger->error(json_encode($data));
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("Merchant ID doesn't match.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

                $order = $this->getOrderByIncrementId($outTradeNo);
                if($order){//validate out_trade_no
                    if($totalFee != $this->helper->formatTotal($order->getBaseGrandTotal(), $order->getBaseCurrencyCode())){ //validate total amount
                        $this->logger->error("Total Fee doesn't match.");
                        $this->logger->error(json_encode($data));
                        $this->notify->SetReturn_code("FAIL");
                        $this->notify->SetReturn_msg("Total Fee doesn't match.");
                        $resultXml = $this->notify->ReplyNotify(false);
                        return $resultRaw->setContents($resultXml);
                    }

                    if($data['result_code'] == "SUCCESS" && $order->getStatus() == "pending"){
                        try{
                            $payment = $order->getPayment();
                            $payment->setAdditionalInformation('transaction_id', $transactionId);
                            $payment->save();

                            $this->invoiceOrderByIncrementId($outTradeNo);
                            return $resultRaw->setContents($resultXml);
                        }catch(\Exception $e){
                            $this->logger->error($e->getMessage());
                            $this->notify->SetReturn_code("FAIL");
                            $this->notify->SetReturn_msg($e->getMessage());
                            $resultXml = $this->notify->ReplyNotify(false);
                            return $resultRaw->setContents($resultXml);
                        }
                    }
                }else{
                    $this->logger->error("Order IncrementId: ".$outTradeNo." does not exist.");
                    $this->notify->SetReturn_code("FAIL");
                    $this->notify->SetReturn_msg("Order IncrementId: ".$outTradeNo." does not exist.");
                    $resultXml = $this->notify->ReplyNotify(false);
                    return $resultRaw->setContents($resultXml);
                }

            }
        }
        return $resultRaw->setContents($resultXml);

    }

    protected function getOrderByIncrementId($incrementId){
        return $this->_orderFactory->create()->loadByIncrementId($incrementId);
    }

}