<?php

namespace MW\Affiliate\Block;

class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var string
     */
    protected $_template = 'MW_Affiliate::link.phtml';

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MW\Affiliate\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_dataHelper->getAffiliateActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('affiliate');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Affiliate Account');
    }
}
