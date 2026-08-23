<?php
namespace MageArray\OrderAttachments\Block;

use MageArray\OrderAttachments\Helper\Data;
use MageArray\OrderAttachments\Model\AttachmentsFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Model\StockFactory;

class MyAttachments extends Template
{
    protected $_storeManager;
    protected $_customerSession;
    protected $urlBuilder;

    /**
     * MyAttachments constructor.
     * @param Context $context
     * @param StockFactory $stockFactory
     * @param Registry $registry
     * @param AttachmentsFactory $attachmentsFactory
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockFactory $stockFactory,
        Registry $registry,
        AttachmentsFactory $attachmentsFactory,
        Session $customerSession,
        Data $dataHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = [ ]
    ) {
        $this->_stockFactory = $stockFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $registry;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_isScopePrivate = true;
        $this->_customerSession = $customerSession;
        $this->_attachmentsFactory = $attachmentsFactory;
        $this->filesystem = $context->getFilesystem();
        $this->_dataHelper = $dataHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
        $custId = $this->_customerSession->getCustomer()->getId();
        $collection = $this->_attachmentsFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $custId);
        $this->setCollection($collection);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()):
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::Class,
                'magearray.outofstocknotification.record.pager'
            )->setAvailableLimit([10=>10,20=>20,50=>50])
                ->setShowPerPage(true)
                ->setCollection(
                    $this->getCollection()->addFieldToFilter('visible_customer_account', 1)
                );
            $this->setChild('pager', $pager);
        endif;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_LINK);
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrderId($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order->getIncrementId();
    }

    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_dataHelper->getMediaPath();
    }
}
