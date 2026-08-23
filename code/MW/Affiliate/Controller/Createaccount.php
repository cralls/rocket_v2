<?php

namespace MW\Affiliate\Controller;

abstract class Createaccount extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Createaccount constructor.
     * @param \Magento\Framework\App\Action\Context $content
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $content,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($content);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_withdrawnFactory = $withdrawnFactory;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->sessionManager = $sessionManager;
        $this->customerFactory =$customerFactory;
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        // Check this module is enabled in frontend and the affiliate is actived
        if ($this->_dataHelper->moduleEnabled()) {

            return parent::dispatch($request);
        } else {
            $this->_forward('noroute');
        }
    }
}
