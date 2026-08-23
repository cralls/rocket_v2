<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

use MW\Affiliate\Model\Transactiontype;

class Affiliatetransaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \MW\Affiliate\Model\Transactiontype
     */
    protected $_transactionType;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory
     * @param \MW\Affiliate\Model\Transactiontype $transactionType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory,
        \MW\Affiliate\Model\Transactiontype $transactionType,
        array $data = []
    ) {
        $this->_transactionFactory = $transactionFactory;
        $this->_transactionType = $transactionType;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['history_id'])) {
            return '';
        }

        $affiliateTransaction = $this->_transactionFactory->create()->load($row['history_id']);
        $type = $affiliateTransaction->getCommissionType();
        $detail = $affiliateTransaction->getOrderId();

        if ($type == Transactiontype::REFERRAL_VISITOR
            || $type == Transactiontype::REFERRAL_SIGNUP
            || $type == Transactiontype::REFERRAL_SUBSCRIBE
        ) {
            $detail = $affiliateTransaction->getCustomerId();
        }

        $transactionDetail = $this->_transactionType->getTransactionDetail($type, $detail, true);

        return $transactionDetail;
    }
}
