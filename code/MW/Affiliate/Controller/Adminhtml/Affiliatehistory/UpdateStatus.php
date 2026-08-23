<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatehistory;

use MW\Affiliate\Model\Status;

class UpdateStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatehistory
{
    /**
     * TODO: Must to re-check
     */
    public function execute()
    {
        $historyIds = (array) $this->getRequest()->getParam('mw_history_id');
        $status     = (int) $this->getRequest()->getParam('status');
        $orderService = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Service\OrderService');
        if (!is_array($historyIds)) {
            $this->messageManager->addError(__('Please select affiliate history(s)'));
        } else {
            $countSuccess = 0;
            $countError = 0;

            try {
                foreach ($historyIds as $historyId) {
                    $history = $this->_objectManager->get(
                        'MW\Affiliate\Model\AffiliatetransactionFactory'
                    )->create()->load($historyId);
                    $orderId = $history->getOrderId();
                    $statusOrder = (int)$history->getStatus();

                    if ($status == Status::CANCELED) {
                        if ($statusOrder == Status::PENDING) {
                            $countSuccess++;
                            $orderService->saveOrderCanceled($orderId);
                        } else {
                            $countError++;
                        };
                    } elseif ($status == Status::COMPLETE) {
                        if ($statusOrder == Status::PENDING) {
                            $countSuccess++;
                            $orderService->saveOrderComplete($orderId);
                        } else {
                            $countError++;
                        };
                    } elseif ($status == Status::CLOSED) {
                        if ($statusOrder == Status::COMPLETE) {
                            $countSuccess++;
                            $orderService->saveOrderClosed($orderId);
                        } else {
                            $countError++;
                        };
                    };
                }

                $statusLabel = Status::getLabel($status);
                if ($countSuccess > 0) {
                    $this->messageManager->addSuccess(__('%1 order(s) have been %2', $countSuccess, $statusLabel));
                }
                if ($countError > 0) {
                    $this->messageManager->addError(__('%1 order(s) cannot be %2', $countError, $statusLabel));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
