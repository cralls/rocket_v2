<?php

namespace MW\Affiliate\Controller\Adminhtml;

abstract class Affiliateparent extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerFactory = $customerFactory;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::parent');
    }
}
