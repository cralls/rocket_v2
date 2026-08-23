<?php

namespace MW\Affiliate\Controller;

abstract class Banner extends \Magento\Framework\App\Action\Action
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
     * @param \Magento\Framework\App\Action\Context $content
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $content,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\Affiliate\Helper\Data $dataHelper
    ) {
        parent::__construct($content);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
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
