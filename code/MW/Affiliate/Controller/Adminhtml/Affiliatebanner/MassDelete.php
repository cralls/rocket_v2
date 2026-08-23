<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

class MassDelete extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Delete multi Affiliate banners
     */
    public function execute()
    {
        $bannerIds = $this->getRequest()->getParam('affiliatebannerGrid');

        if ($bannerIds) {
            try {
                foreach ($bannerIds as $bannerId) {
                    $banner = $this->_bannerFactory->create()->load($bannerId);
                    $banner->delete();
                }

                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully deleted', count($bannerIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/');
    }
}
