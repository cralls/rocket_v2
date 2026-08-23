<?php

namespace MW\Affiliate\Block\Customer\Account\Affiliate;

use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Transactiontype;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \MW\Affiliate\Model\Transactiontype
     */
    protected $_transactionType;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory
     * @param Transactiontype $transactionType
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory,
        \MW\Affiliate\Model\Transactiontype $transactionType,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_transactionFactory = $transactionFactory;
        $this->_transactionType = $transactionType;
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
            'customer_affiliate_history_pager'
        );
        $this->setToolbar($pager);
        $this->getToolbar()->setCollection($this->getAffiliateHistory());

        return $this;
    }

    /**
     * @return \MW\Affiliate\Model\ResourceModel\Affiliatetransaction\Collection
     */
    public function getAffiliateHistory()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $collection = $this->_transactionFactory->create()->getCollection()
            ->addFieldtoFilter('customer_invited', $customerId)
            ->setOrder('transaction_time', 'DESC');

        // Set data for display via frontend
        return $collection;
    }

    /**
     * Retrive collection from toolbar
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

    /**
     * @param $type
     * @return string
     */
    public function getCommissionType($type)
    {
        return Transactiontype::getLabel($type);
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatusText($status)
    {
        return Status::getLabel($status);
    }

    /**
     * @param $type
     * @param $detail
     * @param $customerId
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetail($type, $detail, $customerId)
    {
        if ($type == Transactiontype::REFERRAL_VISITOR
            || $type == Transactiontype::REFERRAL_SIGNUP
            || $type == Transactiontype::REFERRAL_SUBSCRIBE
        ) {
            $detail = $customerId;
        }

        return $this->_transactionType->getTransactionDetail($type, $detail, false);
    }
}
