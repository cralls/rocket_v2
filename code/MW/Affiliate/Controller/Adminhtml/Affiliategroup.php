<?php

namespace MW\Affiliate\Controller\Adminhtml;

abstract class Affiliategroup extends \Magento\Backend\App\Action
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
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;
        $this->_groupFactory = $groupFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
        $this->_dataHelper = $dataHelper;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::group');
    }
}
