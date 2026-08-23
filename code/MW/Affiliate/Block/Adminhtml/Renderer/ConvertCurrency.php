<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class ConvertCurrency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->_pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return float|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $indexColumn =  $this->getColumn()->getIndex();
        $total = $row[$indexColumn];
        return $this->_pricingHelper->currency($total, true, false);
    }
}
