<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportReferralsiteExcel extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Export affiliate invitation report to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_referral.xml';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_affiliate_report_referralsite.grid',
            'grid.export'
        );
        $fileFactory = $this->_objectManager->get(
            'Magento\Framework\App\Response\Http\FileFactory'
        );

        return $fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
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
