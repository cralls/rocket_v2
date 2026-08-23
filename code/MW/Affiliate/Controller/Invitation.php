<?php

namespace MW\Affiliate\Controller;

abstract class Invitation extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;


    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;


    /**
     * Invitation constructor.
     * @param \Magento\Framework\App\Action\Context $content
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $content,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
    ) {
        parent::__construct($content);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
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
            if (!$this->_dataHelper->getAffiliateActive()) {
                $this->_redirect('affiliate/index/createaccount');
                return;
            }

            return parent::dispatch($request);
        } else {
            $this->_forward('noroute');
        }
    }
}
