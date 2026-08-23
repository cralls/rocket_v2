<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class MassStatus extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Update status for multi Affiliate banners
     */
    public function execute()
    {
        $bannerIds = $this->getRequest()->getParam('affiliatebannerGrid');

        if ($bannerIds) {
            $status = $this->getRequest()->getParam('status');

            try {
                foreach ($bannerIds as $bannerId) {
                    $this->_bannerFactory->create()
                        ->load($bannerId)
                        ->setStatus($status)
                        ->save();
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully updated', count($bannerIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
