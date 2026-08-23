<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate\Withdrawn;

use MW\Affiliate\Model\Status;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_storeManager = $context->getStoreManager();
        $this->_withdrawnFactory = $withdrawnFactory;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'customer_affiliate_withdrawn_history_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getWithdrawnHistory());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliatewithdrawn\Collection
     */
    public function getWithdrawnHistory()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $collection = $this->_withdrawnFactory->create()->getCollection()
            ->addFieldtoFilter('customer_id', $customerId)
            ->setOrder('withdrawn_time', 'DESC');

        // Set data for display via frontend
        return $collection;
    }

    /**
     * Retrive collection from toolbar
     */
    public function getCollection()
    {
        return $this->getToolbar()->getCollection();
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getToolbar()->toHtml();
    }

    /**
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatusText($status)
    {
        return Status::getLabel($status);
    }
}
