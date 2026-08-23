<?php
namespace MageArray\OrderAttachments\Block\Sales\Order\View;

use MageArray\OrderAttachments\Helper\Data;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\ProductAlert\Model\StockFactory;

class Fileattachments extends Template
{
    protected $currentCustomer;

    public function __construct(
        Context $context,
        Data $dataHelper,
        StockFactory $stockFactory,
        CurrentCustomer $currentCustomer,
        array $data = [ ]
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->_stockFactory = $stockFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
        $collection = $this->_stockFactory->create()->getCollection();
        $collection->addFieldToFilter('customer_id', $this->currentCustomer->getCustomerId());
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
            )->setCollection(
                $this->getCollection()
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
     * @return mixed
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('orderattachments/index/upload');
    }
    public function inStatuses($orderStatus)
    {
        $statuses = $this->_dataHelper->getOrderStatuses();
        $notAllowed  = explode(',', $statuses);
        return in_array($orderStatus, $notAllowed);
    }
}
