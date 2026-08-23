<?php

namespace MW\Affiliate\Controller\Adminhtml;

abstract class Affiliateprogram extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_storeManager = $storeManager;
        $this->_programFactory = $programFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::program');
    }
}
