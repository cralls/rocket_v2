<?php

namespace Meetanshi\Alipay\Model;

use Magento\Checkout\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Meetanshi\Alipay\Block\Payment\Info;
use Meetanshi\Alipay\Helper\Data as AlipayHelper;

/**
 * Class Payment
 * @package Meetanshi\Alipay\Model
 */
class Payment extends AbstractMethod
{
    /**
     *
     */
    const CODE = 'alipay';

    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var string
     */
    protected $_infoBlockType = Info::class;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var AlipayHelper
     */
    protected $alipayHelper;

    /**
     * Payment constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param PaymentHelper $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ModuleListInterface $moduleList
     * @param TimezoneInterface $localeDate
     * @param OrderFactory $orderFactory
     * @param Url $urlBuilder
     * @param RegionFactory $region
     * @param CountryFactory $country
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param AlipayHelper $alipayHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, ExtensionAttributesFactory $extensionFactory, AttributeValueFactory $customAttributeFactory, PaymentHelper $paymentData, ScopeConfigInterface $scopeConfig, Logger $logger, ModuleListInterface $moduleList, TimezoneInterface $localeDate, OrderFactory $orderFactory, Url $urlBuilder, RegionFactory $region, CountryFactory $country, Session $checkoutSession, StoreManagerInterface $storeManager, AlipayHelper $alipayHelper, AbstractResource $resource = null, AbstractDb $resourceCollection = null, array $data = [])
    {
        $this->urlBuilder = $urlBuilder;
        $this->moduleList = $moduleList;
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->region = $region;
        $this->country = $country;
        $this->logger = $logger;
        $this->alipayHelper = $alipayHelper;

        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data);
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return AbstractMethod
     */
    public function initialize($paymentAction, $stateObject)
    {
        return parent::initialize($paymentAction, $stateObject);
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $available = $this->alipayHelper->isPaymentAvailable();
        if (!$available) {
            return false;
        } else {
            return parent::isAvailable($quote);
        }
    }

    /**
     * @return AbstractMethod
     * @throws LocalizedException
     */
    public function validate()
    {
        return parent::validate();
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->urlBuilder->getUrl('alipay/payment/redirect', ['_secure' => true]);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        return parent::authorize($payment, $amount);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        return parent::capture($payment, $amount);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $refundFormdata = $this->alipayHelper->getRefundAlipayForm($order, $amount);

            $outReturnNo = $refundFormdata['out_return_no'];
            $linkRefund = $this->alipayHelper->buildRequestLink($refundFormdata);

            try {
                $client = new ZendClient();
                $client->setUri($linkRefund);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 6000]);
                $responseBody = $client->request()->getBody();
            } catch (\Exception $e) {
                throw new LocalizedException(__('Curl Exception: ' . $e->getMessage()));
            }

            $object = new \SimpleXMLElement(trim($responseBody));
            if (isset($object->is_success) && ($object->is_success == "T")) {
                $payment->setParentTransactionId($outReturnNo)->setIsTransactionClosed(true)->registerRefundNotification($amount);
            } else {
                $error = isset($object->error) ? $object->error : "";
                throw new LocalizedException(__('Refund Failed: ' . $error));
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Refund Exception: ' . $e->getMessage()));
        }

        return parent::refund($payment, $amount);
    }
}
