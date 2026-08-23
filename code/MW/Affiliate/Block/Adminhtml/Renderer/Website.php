<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\Affiliate\Model\AffiliatewebsitememberFactory
     */
    protected $_websitememberFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory,
        array $data = []
    ) {
        $this->_websitememberFactory = $websitememberFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['customer_id'])) {
            return '';
        }

        $sites = $this->_websitememberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $row['customer_id']]);

        $siteList = [];
        foreach ($sites as $site) {
            $siteList[] = $site->getDomainName();
        }

        return implode(', ', $siteList);
    }
}
