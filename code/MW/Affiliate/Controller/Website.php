<?php

namespace MW\Affiliate\Controller;

abstract class Website extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatewebsitememberFactory
     */
    protected $_websitememberFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $content
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $content,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatewebsitememberFactory $websitememberFactory
    ) {
        parent::__construct($content);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_dataHelper = $dataHelper;
        $this->_websitememberFactory = $websitememberFactory;
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
