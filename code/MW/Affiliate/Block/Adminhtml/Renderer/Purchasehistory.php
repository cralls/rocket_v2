<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Purchasehistory extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

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
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return float|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['order_id'])) {
            return '';
        }

        $collection = $this->_orderFactory->create()->loadByIncrementId($row['order_id']);
        $grandTotal = $collection->getBaseGrandTotal();

        return $this->_pricingHelper->currency($grandTotal, true, false);
    }
}
