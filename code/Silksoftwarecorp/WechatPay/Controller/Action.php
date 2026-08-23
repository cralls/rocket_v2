<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_WechatPay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\WechatPay\Controller;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller Action for WechatPay
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

}
