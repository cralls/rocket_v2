<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Block\Checkout;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

/**
 * Alipay Checkout Pay Block
 *
 */
class Pay extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Silksoftwarecorp\Alipay\Model\AlipayClient
     */
    protected $alipayClient;

    /**
     * @var \Silksoftwarecorp\Alipay\Helper\Data
     */
    protected $helper;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory                 $orderFactory
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Silksoftwarecorp\Alipay\Model\AlipayClient       $alipayClient
     * @param \Silksoftwarecorp\Alipay\Helper\Data              $helper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Silksoftwarecorp\Alipay\Model\AlipayClient $alipayClient,
        \Silksoftwarecorp\Alipay\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->alipayClient = $alipayClient;
        $this->helper = $helper;
    }

    /**
     * Return Alipay Post Charset
     * @return string
     */
    public function getPostCharset(){
        return $this->alipayClient->getPostCharset();
    }

    /**
     * Return form action
     * @return string
     */
    public function getPostUrl(){
        return $this->alipayClient->getGatewayUrl().'?charset='.$this->alipayClient->getPostCharset();
    }

    /**
     * Return current order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder(){
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Return form fields data
     * @return array
     */
    public function getPostData(){
        $order = $this->getOrder();
        try{
            if($this->helper->isMobile()){
                return  $this->alipayClient->getAlipayTradeWapPayFormParams($order->getPayment());
            }else{
                return  $this->alipayClient->getAlipayTradePagePayFormParams($order->getPayment());
            }
        }catch(\Exception $e){
            return [];
        }

    }

    /**
     * Return form html
     * @return string
     */
    public function getFormHtml(){
        $order = $this->getOrder();
        if($this->helper->isMobile()){
            return  $this->alipayClient->alipayTradeWapPay($order->getPayment());
        }else{
            return  $this->alipayClient->alipayTradePagePay($order->getPayment());
        }
    }

}
