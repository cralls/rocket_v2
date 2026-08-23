<?php

namespace MW\Affiliate\Model;

class Typecsv extends \Magento\Framework\DataObject
{
    const ADMIN_CHANGE                 = 1;
    const REFUND_PRODUCT             = 2;
    const CANCEL_WITHDRAWN            = 3;
    const USE_TO_CHECKOUT            = 5;
    const CANCEL_USE_TO_CHECKOUT    = 6;
    const BUY_PRODUCT                = 7;
    const WITHDRAWN                    = 8;
    const REFUND_PRODUCT_AFFILIATE  = 9;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->_withdrawnFactory = $withdrawnFactory;
        $this->_pricingHelper = $pricingHelper;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::ADMIN_CHANGE               => __('Changed By Admin'),
            self::USE_TO_CHECKOUT            => __('Use Credit to Checkout'),
            self::CANCEL_USE_TO_CHECKOUT    => __('Canceled Order using Credit'),
            self::REFUND_PRODUCT            => __('Refund Product'),
            self::WITHDRAWN                    => __('Withdraw'),
            self::CANCEL_WITHDRAWN            => __('Canceled Withdraw'),
            self::BUY_PRODUCT                => __('Referred Customer Buys Product'),
            self::REFUND_PRODUCT_AFFILIATE  => __('Referred Customer Refunds Product')
        ];
    }

    /**
     * @param $type
     * @return string
     */
    public static function getLabel($type)
    {
        $options = self::getOptionArray();

        return $options[$type];
    }

    /**
     * @param $type
     * @param $detail
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetail($type, $detail)
    {
        $result = "";
        switch ($type) {
            case self::ADMIN_CHANGE:
                $result = __($detail);
                break;
            case self::REFUND_PRODUCT:
                $result = __("You refurn to order : #%1, checkout by credit", $detail);
                break;
            case self::USE_TO_CHECKOUT:
                $result = __("You used credit to checkout order : #%1", $detail);
                break;
            case self::CANCEL_USE_TO_CHECKOUT:
                $result = __("You canceled order : #%1, checkout by credit", $detail);
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
                    "Withdrawal money: %1 (Processing Fee: %2)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount(), true, false),
                    $this->_pricingHelper->currency($withdrawn->getFee(), true, false)
                );
                break;
            case self::CANCEL_WITHDRAWN:
                $withdrawn = $this->_withdrawnFactory->create()->load($detail);
                $result = __(
                    "Canceled withdrawal money: %1 (Processing Fee: %2)",
                    $this->_pricingHelper->currency($withdrawn->getWithdrawnAmount(), true, false),
                    $this->_pricingHelper->currency($withdrawn->getFee(), true, false)
                );
                break;
        }

        return $result;
    }
}
