<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatereport\Dashboard;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \MW\RewardPoints\Model\Report
     */
    protected $_report;

    /**
     * @var string
     */
    protected $_template = 'MW_Affiliate::report/dashboard.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \MW\RewardPoints\Model\Report $report
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \MW\Affiliate\Model\Report $report,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_storeManager = $context->getStoreManager();
        $this->_pricingHelper = $pricingHelper;
        $this->_report = $report;
    }

    /**
     * Setting default for every grid on dashboard
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getWebsiteCollection()
    {
        return $this->_storeManager->getWebsites();
    }

    /**
     * @param \Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\Store[]
     */
    public function getGroupCollection(\Magento\Store\Model\Website $website)
    {
        return $website->getGroups();
    }

    /**
     * @param \Magento\Store\Model\Group $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection[]
     */
    public function getStoreCollection(\Magento\Store\Model\Group $group)
    {
        return $group->getStores();
    }

    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function getHintHtml()
    {
        $html = '';
        $url = $this->getHintUrl();
        if ($url) {
            $html = '<a'
                . ' href="'. $this->escapeUrl($url) . '"'
                . ' onclick="this.target=\'_blank\'"'
                . ' title="' . __('What is this?') . '"'
                . ' class="link-store-scope">'
                . __('What is this?')
                . '</a>';
        }

        return $html;
    }

    /**
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPricingHelper()
    {
        return $this->_pricingHelper;
    }
}
