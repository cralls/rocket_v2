<?php

namespace MW\Affiliate\Block\Customer\Account;

use MW\Affiliate\Model\Orderstatus;
use MW\Affiliate\Model\Transactiontype;

class Credit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \MW\Affiliate\Model\Transactiontype
     */
    protected $_transactionType;

    /**
     * Credit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param Transactiontype $transactionType
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \MW\Affiliate\Model\Transactiontype $transactionType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_transactionType = $transactionType;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * @return \MW\Affiliate\Model\Affiliatecustomers
     */
    public function getAffiliateMemberModel()
    {
        return $this->_affiliatecustomersFactory->create();
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function getCustomerCredit($customerId)
    {
        return $this->_creditcustomerFactory->create()->load($customerId);
    }

    /**
     * @param $type
     * @return \Magento\Framework\Phrase|string
     */
    public function getTypeLabel($type)
    {
        return Transactiontype::getLabel($type);
    }

    /**
     * @param $type
     * @param $detail
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetail($type, $detail)
    {
        return $this->_transactionType->getTransactionDetail($type, $detail, false);
    }

    /**
     * @param $status
     * @return \Magento\Framework\Phrase|string
     */
    public function getStatusText($status)
    {
        return Orderstatus::getLabel($status);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Theme\Block\Html\Pager */
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'transaction_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getCreditHistory());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Credithistory\Collection
     */
    public function getCreditHistory()
    {
        $customerId = (int) $this->getCurrentCustomer()->getId();
        $collection = $this->_credithistoryFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', ['neq' => Orderstatus::HOLDING])
            ->setOrder('created_time', 'DESC')
            ->setOrder('credit_history_id', 'DESC');

        return $collection;
    }

    /**
     * Retrive credit collection from toolbar
     */
    public function getCollection()
    {
        return $this->getToolbar()->getCollection();
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getToolbar()->toHtml();
    }
}
