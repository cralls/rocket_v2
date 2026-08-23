<?php
namespace VNS\Admin\Controller\Adminhtml\Orders;

class MassInvoice extends \Magento\Backend\App\Action
{
    protected $orderRepository;
    protected $invoiceService;
    protected $transaction;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction
        ) {
            parent::__construct($context);
            $this->orderRepository = $orderRepository;
            $this->invoiceService = $invoiceService;
            $this->transaction = $transaction;
    }
    
    public function execute()
    {
        $orderIds = $this->getRequest()->getParam('selected');
        $created = 0;
        
        foreach ($orderIds as $orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
                if ($order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->register();
                    
                    // Start transaction to save invoice and order
                    $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                    
                    // Only set the order to processing if the invoice is saved successfully
                    if ($invoice->save()) {
                        // Change order status to Processing
                        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                        ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $this->orderRepository->save($order);
                        $created++;
                    }
                    
                    // Complete the transaction
                    $this->transaction->save();
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        
        if ($created > 0) {
            $this->messageManager->addSuccessMessage(__('%1 invoice(s) were created.', $created));
        } else {
            $this->messageManager->addErrorMessage(__('No invoices were created.'));
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VNS_Admin::order');
    }
}
