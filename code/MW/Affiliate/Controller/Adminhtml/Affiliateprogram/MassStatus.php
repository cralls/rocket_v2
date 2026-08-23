<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliateprogram;

class MassStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliateprogram
{
    /**
     * Update status for multi Affiliate programs
     */
    public function execute()
    {
        $programIds = $this->getRequest()->getParam('affiliateprogramGrid');

        if ($programIds) {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($programIds as $programId) {
                    $this->_programFactory->create()
                        ->load($programId)
                        ->setStatus($status)
                        ->save();
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($programIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
