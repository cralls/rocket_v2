<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class WebsiteReport extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_websiteFactory = $websiteFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (sizeof($row->getWebsiteId()) > 1) {
            $result = '';
            $websiteCollection = $this->_websiteFactory->create()->getCollection()
                ->addFieldToFilter('website_id', ['in' => $row->getWebsiteId()]);

            foreach ($websiteCollection as $webste) {
                $result .= $webste->getName();
            }

            return $result;
        } else {
            $websiteId = $row->getWebsiteId();
            $website = $this->_websiteFactory->create()->load($websiteId[0]);

            return $website->getName();
        }
    }
}
