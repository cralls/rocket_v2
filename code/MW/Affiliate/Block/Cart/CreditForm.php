<?php

namespace MW\Affiliate\Block\Cart;

class CreditForm extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MW\Affiliate\Helper\Data
     */
    public $_helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MW\Affiliate\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\Affiliate\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_helper = $helper;
        $this->_storeManager = $context->getStoreManager();
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     *
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }


    /**
     *
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
