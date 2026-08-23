<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatewithdrawnpending;

class Withdrawnedit extends \MW\Affiliate\Controller\Adminhtml\Affiliatewithdrawnpending
{
    public function execute()
    {
        $withdrawnIds = (array) $this->getRequest()->getParam('affiliate_withdrawn_pending');
        $status       = (int) $this->getRequest()->getParam('status');

        if (!is_array($withdrawnIds)) {
            $this->messageManager->addError(__('Please select withdrawn(s)'));
        } else {
            try {
                $this->_objectManager->get('MW\Affiliate\Helper\Data')
                    ->processWithdrawn($status, $withdrawnIds);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
