<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Credittransaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \MW\Affiliate\Model\Transactiontype
     */
    protected $_transactionType;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param \MW\Affiliate\Model\Transactiontype $transactionType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \MW\Affiliate\Model\Transactiontype $transactionType,
        array $data = []
    ) {
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_transactionType = $transactionType;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['credit_history_id'])) {
            return '';
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $action     = $request->getActionName();
        $credithistory = $this->_credithistoryFactory->create()->load($row['credit_history_id']);
        if ($action == "exportCsv" || $action == "exportXml") {
            $transactionDetail = $this->_transactionType->getTransactionDetailLabel(
                $credithistory->getTypeTransaction(),
                $credithistory->getTransactionDetail()
            );
        } else {
            $transactionDetail = $this->_transactionType->getTransactionDetail(
                $credithistory->getTypeTransaction(),
                $credithistory->getTransactionDetail(),
                true
            );
        }
        return $transactionDetail;
    }
}
