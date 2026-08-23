<?php


namespace Magenest\Xero\Controller\Adminhtml\MassQueue;

use Magento\Framework\Controller\ResultFactory;

class CreditMemo extends AbstractMassQueue
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    protected $_enable = "magenest_xero_config/xero_credit/enabled";

    protected $_type = "CreditNote";

    protected $_id = 'increment_id';

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        parent::execute();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        }
        return $resultRedirect->setPath('sales/creditmemo/');
    }
}
