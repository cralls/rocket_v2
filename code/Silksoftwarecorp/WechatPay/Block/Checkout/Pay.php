<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Block\Checkout;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

/**
 * WechatPay Checkout Pay Block
 *
 */
class Pay extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_orderFactory;


    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Silksoftwarecorp\WechatPay\Model\Method\WechatPay
     */
    protected $paymentMethod;

    /**
     * @var \Silksoftwarecorp\WechatPay\Helper\Data
     */
    protected $helper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \Magento\Checkout\Model\Session                       $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory                     $orderFactory
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Silksoftwarecorp\WechatPay\Model\Method\WechatPay    $paymentMethod
     * @param \Silksoftwarecorp\WechatPay\Helper\Data               $helper
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Silksoftwarecorp\WechatPay\Model\Method\WechatPay $paymentMethod,
        \Silksoftwarecorp\WechatPay\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->paymentMethod = $paymentMethod;
        $this->helper = $helper;
    }


    /**
     * Return current order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder(){
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * getPaymentInfo from gateway
     * @return array
     */
    public function getPaymentInfo(){
        $order = $this->getOrder();
        $paymentInfo = $this->paymentMethod->getPayPaymentInfo($order);
        return $paymentInfo;
    }



}
