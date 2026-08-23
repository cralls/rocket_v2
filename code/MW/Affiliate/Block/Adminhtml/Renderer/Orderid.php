<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Orderid extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['order_id'])) {
            return '';
        }

        $order = $this->_orderFactory->create()->loadByIncrementId($row['order_id']);
        $url = "sales/order/view";
        $result = __(
            '<b><a href="%1">%2</a></b>',
            $this->getUrl($url, ['order_id' => $order->getId()]),
            $row['order_id']
        );

        return $result;
    }
}
