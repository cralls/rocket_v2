<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Controller;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller Action for Alipay
 */
abstract class Action extends \Magento\Framework\App\Action\Action
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
     * @var \Silksoftwarecorp\Alipay\Model\AlipayClient
     */
    protected $alipayClient;

    /**
     * @var \Silksoftwarecorp\Alipay\Model\Logger
     */
    protected $logger;

    /**
     * @var \Silksoftwarecorp\Alipay\Helper\Data
     */
    protected $helper;

    /**
     *
     * @param \Magento\Framework\App\Action\Context             $context
     * @param \Magento\Checkout\Model\Session                   $checkoutSession
     * @param \Magento\Framework\Registry                       $coreRegistry
     * @param \Magento\Sales\Model\OrderFactory                 $orderFactory
     * @param \Magento\Sales\Api\InvoiceOrderInterface          $invoiceOrder
     * @param \Magento\Framework\View\ResultPageFactory         $resultPageFactory
     * @param \Magento\Framework\View\ResultLayoutFactory       $resultLayoutFactory
     * @param \Magento\Framework\Controller\ResultRawFactory    $resultRawFactory
     * @param \Magento\Framework\Controller\ResultJsonFactory   $resultJsonFactory
     * @param \Silksoftwarecorp\Alipay\Model\AlipayClient       $alipayClient
     * @param \Silksoftwarecorp\Alipay\Model\Logger             $logger
     * @param \Silksoftwarecorp\Alipay\Helper\Data              $helper
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
        \Silksoftwarecorp\Alipay\Model\AlipayClient $alipayClient,
        \Silksoftwarecorp\Alipay\Model\Logger $logger,
        \Silksoftwarecorp\Alipay\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_invoiceOrder = $invoiceOrder;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->alipayClient = $alipayClient;
        $this->helper = $helper;
    }

    /**
     * invoice order by order increment_id
     * @param  string $incrementId
     */
    protected function invoiceOrderByIncrementId($incrementId){
        $order = $this->getOrderByIncrementId($incrementId);
        if($order){
                $payment = $order->getPayment();
                $payment->setAdditionalInformation('trade_no', $this->getRequest()->getParam('trade_no'));
                $payment->setAdditionalInformation('seller_id', $this->getRequest()->getParam('seller_id'));
                $payment->save();
                if($order->canInvoice()){
                    $invoiceId = $this->_invoiceOrder->execute($order->getId(), true);
                }


        }
    }

    protected function getOrderByIncrementId($incrementId){
        return $this->_orderFactory->create()->loadByIncrementId($incrementId);
    }

}
