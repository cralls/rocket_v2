<?php

namespace MW\Affiliate\Model;

class Transactiontype extends \Magento\Framework\DataObject
{
    const ADMIN_CHANGE                 = 1;
    const REFUND_PRODUCT             = 2;
    const CANCEL_WITHDRAWN            = 3;
    const USE_TO_CHECKOUT            = 5;
    const CANCEL_USE_TO_CHECKOUT    = 6;
    const BUY_PRODUCT                = 7;
    const WITHDRAWN                    = 8;
    const REFUND_PRODUCT_AFFILIATE  = 9;
    const REFERRAL_VISITOR            = 10;
    const REFERRAL_SIGNUP            = 11;
    const REFERRAL_SUBSCRIBE        = 12;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_pricingHelper = $pricingHelper;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_dataHelper = $dataHelper;
        $this->_withdrawnFactory = $withdrawnFactory;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::ADMIN_CHANGE               => __('Updated by Admin'),
            self::USE_TO_CHECKOUT            => __('Use Credit to Checkout'),
            self::CANCEL_USE_TO_CHECKOUT    => __('Canceled Order using Credit'),
            self::REFUND_PRODUCT            => __('Refund Product'),
            self::WITHDRAWN                    => __('Withdraw'),
            self::CANCEL_WITHDRAWN            => __('Canceled Withdraw'),
            self::BUY_PRODUCT                => __('Referral Purchase'),
            self::REFUND_PRODUCT_AFFILIATE  => __('Referred Customer Receives Refund'),
            self::REFERRAL_VISITOR            => __('Referral Visitor'),
            self::REFERRAL_SIGNUP            => __('Referral Sign-up'),
            self::REFERRAL_SUBSCRIBE        => __('Referral Subscribe')
        ];
    }

    /**
     * @return array
     */
    public static function getAffiliateTypeArray()
    {
        return [
            self::BUY_PRODUCT            => __('Referral Purchase'),
            self::REFERRAL_VISITOR        => __('Referral Visitor'),
            self::REFERRAL_SIGNUP        => __('Referral Sign-up'),
            self::REFERRAL_SUBSCRIBE    => __('Referral Subscribe')
        ];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getLabel($type)
    {
        $options = self::getOptionArray();

        return $options[$type];
    }

    /**
     * @param $type
     * @param $detail
     * @param bool|false $isAdmin
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetail($type, $detail, $isAdmin = false)
    {
        $result = "";
        if ($isAdmin) {
            $url = "sales/order/view";
        } else {
            $url = "sales/order/view";
        }

        switch ($type) {
            case self::ADMIN_CHANGE:
                $result = __($detail);
                break;
            case self::REFUND_PRODUCT:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $result = __(
                    "You refurn to order : <b><a href=\"%1\">#%2</a></b>, checkout by credit",
                    $this->_urlBuilder->getUrl($url, ['order_id' => $order->getId()]),
                    $detail
                );
                break;
            case self::USE_TO_CHECKOUT:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $result = __(
                    "You used credit to checkout order : <b><a href=\"%1\">#%2</a></b>",
                    $this->_urlBuilder->getUrl($url, ['order_id' => $order->getId()]),
                    $detail
                );
                break;
            case self::CANCEL_USE_TO_CHECKOUT:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                $result = __(
                    "You cancelled order : <b><a href=\"%1\">#%2</a></b>, checkout by credit",
                    $this->_urlBuilder->getUrl($url, ['order_id'=>$order->getId()]),
                    $detail
                );
                break;
            case self::BUY_PRODUCT:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                if ($isAdmin) {
                    $result = __(
                        "Commission from order : <b><a href=\"%1\">#%2</a></b>",
                        $this->_urlBuilder->getUrl($url, ['order_id' => $order->getId()]),
                        $detail
                    );
                } else {
                    $result = __("Commission from order: <b>#%1</b>", $detail);
                }
                break;
            case self::REFUND_PRODUCT_AFFILIATE:
                $order = $this->_orderFactory->create()->loadByIncrementId($detail);
                if ($isAdmin) {
                    $result = __(
                        "Customer granted refund. Order <b><a href=\"%1\">#%2</a></b>. Affiliate commission reversed.",
                        $this->_urlBuilder->getUrl($url, ['order_id'=>$order->getId()]),
                        $detail
                    );
                } else {
                    $result = __("Customer granted refund. Order <b>#%1</b>. Affiliate commission reversed.", $detail);
                }
                break;
            case self::WITHDRAWN:
                $withdrawn = $this->_withdrawnFactory->create()->load($detail);
                $result = __(
                    "Commission Withdrawal: <b>%1</b> (Processing Fee: <b>%2</b>)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount()),
                    $this->_pricingHelper->currency($withdrawn->getFee())
                );
                break;
            case self::CANCEL_WITHDRAWN:
                $withdrawn = $this->_withdrawnFactory->create()->load($detail);
                $result = __(
                    "Canceled Commission Withdrawal: <b>%1</b> (Processing Fee: <b>%2</b>)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount()),
                    $this->_pricingHelper->currency($withdrawn->getFee())
                );
                break;
            case self::REFERRAL_VISITOR:
                $visitorNo = $this->_dataHelper->getReferralVisitorNumber(
                    $this->_storeManager->getStore()->getCode()
                );
                $plural = ($visitorNo > 1) ? 's' : '';
                $result = __('Commission of %1 referral visitor' . $plural, $visitorNo);
                break;
            case self::REFERRAL_SIGNUP:
                if ($detail =='') {
                    $detail = 0;
                }
                $email = $this->_customerFactory->create()->load($detail)->getEmail();
                $result = __('Commission of referral sign-up: <b>%1</b>', $email);
                break;
            case self::REFERRAL_SUBSCRIBE:
                if ($detail =='') {
                    $detail = 0;
                }
                $email = $this->_customerFactory->create()->load($detail)->getEmail();
                $result = __('Commission of referral subscriber: <b>%1</b>', $email);
                break;
        }

        return $result;
    }

    public function getTransactionDetailLabel($type, $detail)
    {
        $result = "";
        switch ($type) {
            case self::ADMIN_CHANGE:
                $result = __($detail);
                break;
            case self::REFUND_PRODUCT:
                $result = __(
                    "You refurn to order : #%1, checkout by credit",
                    $detail
                );
                break;
            case self::USE_TO_CHECKOUT:
                $result = __(
                    "You used credit to checkout order : #%1",
                    $detail
                );
                break;
            case self::CANCEL_USE_TO_CHECKOUT:
                $result = __(
                    "You cancelled order : #%1, checkout by credit",
                    $detail
                );
                break;
            case self::BUY_PRODUCT:
                $result = __("Commission from order: #%1", $detail);
                break;
            case self::REFUND_PRODUCT_AFFILIATE:
                $result = __("Customer granted refund. Order #%1. Affiliate commission reversed.", $detail);
                break;
            case self::WITHDRAWN:
                $withdrawn = $this->_withdrawnFactory->create()->load($detail);
                $result = __(
                    "Commission Withdrawal: %1 (Processing Fee: %2)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount()),
                    $this->_pricingHelper->currency($withdrawn->getFee())
                );
                break;
            case self::CANCEL_WITHDRAWN:
                $withdrawn = $this->_withdrawnFactory->create()->load($detail);
                $result = __(
                    "Canceled Commission Withdrawal: %1 (Processing Fee: %2)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount()),
                    $this->_pricingHelper->currency($withdrawn->getFee())
                );
                break;
            case self::REFERRAL_VISITOR:
                $visitorNo = $this->_dataHelper->getReferralVisitorNumber(
                    $this->_storeManager->getStore()->getCode()
                );
                $plural = ($visitorNo > 1) ? 's' : '';
                $result = __('Commission of %1 referral visitor' . $plural, $visitorNo);
                break;
            case self::REFERRAL_SIGNUP:
                if ($detail =='') {
                    $detail = 0;
                }
                $email = $this->_customerFactory->create()->load($detail)->getEmail();
                $result = __('Commission of referral sign-up: %1', $email);
                break;
            case self::REFERRAL_SUBSCRIBE:
                if ($detail =='') {
                    $detail = 0;
                }
                $email = $this->_customerFactory->create()->load($detail)->getEmail();
                $result = __('Commission of referral subscriber: %1', $email);
                break;
        }

        return $result;
    }
}
