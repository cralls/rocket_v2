<?php

namespace MW\Affiliate\Model\Quote\Address\Total\Credit;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_adminOrder;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Discount constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\AdminOrder\Create $adminOrder
     * @param \MW\Affiliate\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\AdminOrder\Create $adminOrder,
        \MW\Affiliate\Helper\Data $dataHelper
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManger;
        $this->_request = $request;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionManager = $sessionManager;
        $this->_state = $state;
        $this->_customerSession = $customerSession;
        $this->_localeDate = $localeDate;
        $this->_adminOrder = $adminOrder;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {

        parent::collect($quote, $shippingAssignment, $total);
        $store_id = $this->_storeManager->getStore()->getId();
        $storeCode = $this->_storeManager->getStore($store_id)->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            $baseDiscountAmount = $this->_checkoutSession->getCredit();
            if ($baseDiscountAmount != 0) {

                $address = $shippingAssignment->getShipping()->getAddress();
                //$quote = $address->getQuote();
                $items = $address->getAllVisibleItems();
                if (!count($items)) {
                    return $this;
                }
                $creditDicountForItems = $baseDiscountAmount;
                foreach ($items as $item) {
                    $basePrice = $item->getBasePrice();
                    $creditDicountForItem = min($creditDicountForItems, $basePrice);
                    $creditDicountForItems -= $creditDicountForItem;
                    $item->setDiscountAmount($item->getDiscountAmount() + $this->_priceCurrency->convert($creditDicountForItem));
                    $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $creditDicountForItem);
                    $item->setCreditDiscount($creditDicountForItem);
                }

                $label = 'Credit';
                $discountAmount = $this->_priceCurrency->convert($baseDiscountAmount);
                $discountAmountAff = $discountAmount;
                $baseDiscountAmountAff = $baseDiscountAmount;
                if ($total->getDiscountAmount() != 0) {
                    $baseDiscountAmount = abs($total->getDiscountAmount()) + $baseDiscountAmount;
                    $discountAmount = $this->_priceCurrency->convert($baseDiscountAmount);
                    $label = $total->getDiscountDescription() . ', ' . $label;
                }

                $total->setDiscountDescription($label);
                $total->setDiscountAmount($discountAmount);
                $total->setBaseDiscountAmount($baseDiscountAmount);
                $total->setSubtotalWithDiscount($total->getSubtotal() - $discountAmountAff);
                $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() - $baseDiscountAmountAff);

                $total->addTotalAmount($this->getCode(), -$discountAmount);
                $total->addBaseTotalAmount($this->getCode(), -$baseDiscountAmount);
                $total->setGrandTotal($total->getGrandTotal() - $discountAmountAff);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscountAmountAff);
            }
            return $this;
        }
    }
}
