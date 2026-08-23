<?php

namespace MW\Affiliate\Controller\Adminhtml;

abstract class Affiliatememberpending extends \Magento\Backend\App\Action
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
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerFactory = $customerFactory;
        $this->_storeFactory = $storeFactory;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::pending');
    }
}
