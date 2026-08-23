<?php

namespace MW\Affiliate\Controller\Adminhtml;

abstract class Affiliatebanner extends \Magento\Backend\App\Action
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
     * @var \MW\Affiliate\Model\AffiliatebannerFactory
     */
    protected $_bannerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatebannermemberFactory
     */
    protected $_bannermemberFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory
     * @param \MW\Affiliate\Model\AffiliatebannermemberFactory $bannermemberFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Affiliate\Model\AffiliatebannerFactory $bannerFactory,
        \MW\Affiliate\Model\AffiliatebannermemberFactory $bannermemberFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;
        $this->_bannerFactory = $bannerFactory;
        $this->_bannermemberFactory = $bannermemberFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::banner');
    }
}
