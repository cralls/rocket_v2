<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatereport;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportSalesExcel extends \MW\Affiliate\Controller\Adminhtml\Affiliatereport
{
    /**
     * Export affiliate sales report to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_sales.xml';
        $content = $this->_view->getLayout()->getChildBlock(
            'mw_affiliate_report_result.grid',
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
        return $this->_authorization->isAllowed('MW_Affiliate::report_sales');
    }
}
