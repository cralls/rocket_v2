<?php

namespace Meetanshi\Alipay\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Data
 * @package Meetanshi\Alipay\Helper
 */
class Data extends AbstractHelper
{
    const SECRET_KEY_NAME = "mage_key";
    const REFUND_SERVICE = "forex_refund";
    const SECRET_KEY = "secret_key";
    const OUT_TRADE_NO = "trade_no";
    const HTML_DATA = "html";
    /**
     * @var Http
     */
    protected $request;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Repository
     */
    private $repository;
    /**
     * @var RequestInterface
     */
    private $requestInterface;
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var
     */
    private $parameter;
    /**
     * @var string
     */
    private $signType = 'MD5';
    /**
     * @var string
     */
    private $inputCharset = 'utf-8';
    /**
     * @var string
     */
    private $cacert;
    /**
     * @var string
     */
    private $service = "create_forex_trade";
    /**
     * @var
     */
    private $outTradeNo;
    /**
     * @var
     */
    private $subject;
    /**
     * @var
     */
    private $total;
    /**
     * @var
     */
    private $currency;

    private $quoteFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManager
     * @param Repository $repository
     * @param RequestInterface $requestInterface
     * @param Http $request
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(Context $context, EncryptorInterface $encryptor, DirectoryList $directoryList, StoreManagerInterface $storeManager, Repository $repository, RequestInterface $requestInterface, Http $request, QuoteFactory $quoteFactory)
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->cacert = getcwd() . "/app/code/Meetanshi/Alipay" . '\\cacert.pem';
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->requestInterface = $requestInterface;
        $this->request = $request;
        $this->repository = $repository;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->scopeConfig->getValue('payment/alipay/active', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getPaymentInstructions()
    {
        return $this->scopeConfig->getValue('payment/alipay/instructions', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function isAutoInvoice()
    {
        return $this->scopeConfig->getValue('payment/alipay/allow_invoice', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isPaymentAvailable()
    {
        $partnerkey = trim($this->getPartnerKey());
        $partnerid = trim($this->getPartnerId());
        $url = trim($this->getApiEndpoint());
        if ((!$partnerkey) | (!$partnerid) | (!$url)) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getPartnerKey()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue('payment/alipay/partner_secret', ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getPartnerId()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue('payment/alipay/partner_id', ScopeInterface::SCOPE_STORE));
    }

    /**
     * @return string
     */
    public function getApiEndpoint()
    {
        $endpoint = $this->scopeConfig->getValue('payment/alipay/testmode', ScopeInterface::SCOPE_STORE);
        if ($endpoint) {
            return trim($this->scopeConfig->getValue('payment/alipay/endpoint_test', ScopeInterface::SCOPE_STORE));
        } else {
            return trim($this->scopeConfig->getValue('payment/alipay/endpoint_production', ScopeInterface::SCOPE_STORE));
        }
    }

    /**
     * @param $order
     * @param $secretKey
     * @return string
     */
    public function getPaymentForm($order, $secretKey)
    {
        $this->outTradeNo = $order->getIncrementId() . "_" . $this->generateRandomString();
        $this->subject = $this->getPaymentSubject();
        $this->total = $order->getGrandTotal();
        $this->currency = $order->getOrderCurrencyCode();
        $notifyUrl = $this->getNotifyUrl();
        $notifyUrl .= self::SECRET_KEY_NAME . "=$secretKey";

        $quote = $this->quoteFactory->create()->load($order->getQuoteId());

        $items = $quote->getAllVisibleItems();
        $itemQty = 0;
        foreach ($items as $item) {
            $itemName [] = $item->getName();
            $itemQty += $item->getQty();
        }

        $iName = implode("|", $itemName);
        $totalQty = $itemQty;
        $trade = ['business_type' => '4', 'goods_info' => $iName, 'total_quantity' =>$totalQty];
        $tradeInformation = json_encode($trade);

        $referUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $this->parameter = ["service" => $this->getService(), "product_code" => 'NEW_OVERSEAS_SELLER', "partner" => $this->getPartnerId(), "refer_url" => $referUrl,"trade_information" => $tradeInformation, "notify_url" => $notifyUrl, "return_url" => $this->getReturnUrl(), "out_trade_no" => $this->outTradeNo, "subject" => $this->subject, "total_fee" => number_format($this->total, 2), "currency" => $this->currency, "_input_charset" => trim(strtolower($this->getInputCharset()))];
        return $this->buildAlipayRequestForm($this->parameter, "get", "确认");
    }

    /**
     * @return string
     */
    public function getPaymentSubject()
    {
        $subject = trim($this->scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE));
        if (!$subject) {
            return "Magento 2 order";
        }

        return $subject;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNotifyUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "alipay/payment/notify?";
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReturnUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        return $baseUrl . "alipay/payment/success";
    }

    /**
     * @return string
     */
    public function getInputCharset()
    {
        return $this->inputCharset;
    }

    /**
     * @param $para_temp
     * @param $method
     * @param $button_name
     * @return string
     */
    public function buildAlipayRequestForm($para_temp, $method, $button_name)
    {
        $this->alipay_gateway = $this->getApiEndpoint();

        $para = $this->buildAlipayRequestPara($para_temp);

        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . $this->alipay_gateway . "_input_charset=" . trim(strtolower($this->getInputCharset())) . "' method='" . $method . "'>";
        foreach ($para as $key => $val) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }

        $sHtml = $sHtml . "<input type='submit'  value='" . $button_name . "' style='display:none;'></form>";

        return $sHtml;
    }

    /**
     * @param $para_temp
     * @return mixed
     */
    public function buildAlipayRequestPara($para_temp)
    {

        $para_filter = $this->paraFilter($para_temp);

        $para_sort = $this->argSort($para_filter);

        $mysign = $this->buildRequestMysign($para_sort);

        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim($this->getSignType()));

        return $para_sort;
    }

    /**
     * @param $para
     * @return array
     */
    public function paraFilter($para)
    {
        $para_filter = [];
        foreach ($para as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * @param $para
     * @return mixed
     */
    public function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * @param $para_sort
     * @return string
     */
    public function buildRequestMysign($para_sort)
    {
        $prestr = $this->createLinkstring($para_sort);

        $mysign = "";
        switch (strtoupper(trim($this->getSignType()))) {
            case "MD5":
                $mysign = $this->md5Sign($prestr, $this->getPartnerKey());
                break;
            default:
                $mysign = "";
        }

        return $mysign;
    }

    /**
     * @param $para
     * @return false|string
     */
    public function createLinkstring($para)
    {
        $arg = '';
        foreach ($para as $key => $val) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        if ($arg) {
            $arg = substr($arg, 0, -1);
        }

        $arg = stripslashes($arg);
        return $arg;
    }

    /**
     * @return string
     */
    public function getSignType()
    {
        return $this->signType;
    }

    /**
     * @param $prestr
     * @param $key
     * @return string
     */
    public function md5Sign($prestr, $key)
    {
        $prestr = $prestr . $key;

        return hash('md5', $prestr);
    }

    /**
     * @param $order
     * @param $amount
     * @return mixed
     */
    public function getRefundAlipayForm($order, $amount)
    {
        $payment = $order->getPayment();
        $outTradeNo = $payment->getParentTransactionId();
        $data = $payment->getAdditionalInformation();
        $currency = $order->getOrderCurrencyCode();
        $outReturnNo = $outTradeNo . $this->generateRandomString();
        $parameter = ["service" => self::REFUND_SERVICE, "partner" => $this->getPartnerId(), "out_trade_no" => $data['out_trade_no'], "out_return_no" => $outReturnNo, "return_amount" => $amount, "_input_charset" => "UTF-8", "reason" => "Magento2RefundOrder" . $order->getIncrementId(), "gmt_return" => date("Ymdhis"), 'currency' => $currency];

        $para = $this->buildAlipayRequestPara($parameter);

        return $para;
    }

    /**
     * @param $parameter
     * @return string
     */
    public function buildRequestLink($parameter)
    {
        $url = $this->getApiEndpoint();
        foreach ($parameter as $k => $v) {
            $url .= $k . "=" . $v . "&";
        }

        return $url;
    }

    /**
     * @param string $orderNum
     * @return string
     */
    public function genSecretKey($orderNum = "")
    {
        $secretWordConfig = $this->generateRandomString(10);
        $secretWord = time() . $orderNum . $secretWordConfig;
        return hash('md5', $secretWord);
    }

    /**
     * @return mixed
     */
    public function showLogo()
    {
        return $this->scopeConfig->getValue('payment/alipay/show_logo', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getPaymentLogo()
    {
        $params = ['_secure' => $this->request->isSecure()];
        return $this->repository->getUrlWithParams('Meetanshi_Alipay::images/alipay.png', $params);
    }

    /**
     * @param int $length
     * @return string
     */
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @return bool
     */
    public function verifyReturn()
    {
        $params = $this->request->getParams();
        if (empty($params)) {
            return false;
        } else {
            $isSign = $this->getSignVeryfy($params, $params["sign"]);
            return $isSign;
        }
    }

    /**
     * @param $para_temp
     * @param $sign
     * @return bool
     */
    public function getSignVeryfy($para_temp, $sign)
    {
        $para_filter = $this->paraFilter($para_temp);

        $para_sort = $this->argSort($para_filter);

        $prestr = $this->createLinkstring($para_sort);

        $isSgin = false;
        switch (strtoupper(trim($this->getSignType()))) {
            case "MD5":
                $isSgin = $this->md5Verify($prestr, $sign, $this->getPartnerKey());
                break;
            default:
                $isSgin = false;
        }

        return $isSgin;
    }

    /**
     * @param $prestr
     * @param $sign
     * @param $key
     * @return bool
     */
    public function md5Verify($prestr, $sign, $key)
    {
        $prestr = $prestr . $key;
        $mysgin = hash('md5', $prestr);

        if ($mysgin == $sign) {
            return true;
        } else {
            return false;
        }
    }
}
