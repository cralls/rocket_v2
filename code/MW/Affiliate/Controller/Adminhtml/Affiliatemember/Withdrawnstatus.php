<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

class Withdrawnstatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Change status of Withdrawns
     */
    public function execute()
    {
        $withdrawnIds = $this->getRequest()->getParam('affiliate_member_withdrawn');
        if (!is_array($withdrawnIds)) {
            $this->messageManager->addError(__('Please select withdrawn(s)'));
        } else {
            $status = (int)$this->getRequest()->getParam('mass_status');

            try {
                $this->_dataHelper->processWithdrawn($status, $withdrawnIds);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/edit/id/' . $this->getRequest()->getParam('id'));
    }
}
