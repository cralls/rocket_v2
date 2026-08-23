<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportReferralCsv extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Export affiliate invitation report to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_referral.csv';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_affiliate_report_referral.grid',
            'grid.export'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_Affiliate::referral');
    }
}
