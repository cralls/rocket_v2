<?php

namespace MW\Affiliate\Controller\Credit;

abstract class IndexAbstract extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_affiliateHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $content,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\Affiliate\Helper\Data $affiliateHelper
    ) {
        parent::__construct($content);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_affiliateHelper = $affiliateHelper;
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
        if ($this->_affiliateHelper->moduleEnabled()) {
            if ($this->_affiliateHelper->getAffiliateActive()) {
                if ($this->_affiliateHelper->getAffiliateLock()) {
                    $this->messageManager->addError('Your affiliate account is locked');
                    $this->_redirect('affiliate/index/referralaccount');
                    return;
                }

                return parent::dispatch($request);
            } else {
                $this->_redirect('affiliate/index/createaccount');
            }
        } else {
            $this->_forward('noroute');
        }
    }
}
