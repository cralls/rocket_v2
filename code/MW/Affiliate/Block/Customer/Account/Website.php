<?php

namespace MW\Affiliate\Block\Customer\Account;

class Website extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\AffiliatewebsitememberFactory
     */
    protected $_websitememberFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_websitememberFactory = $websitememberFactory;
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
            'customer_affiliate_website_list_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getWebsiteCollection());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliatewebsitemember\Collection
     */
    public function getWebsiteCollection()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $collection = $this->_websitememberFactory->create()->getCollection()
            ->addFieldtoFilter('customer_id', $customerId);
        $collection->setOrder('status', 'ASC');

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
}
