<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatememberpending;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MW\Affiliate\Controller\Adminhtml\Affiliatememberpending
{
    /**
     * Export affiliate member pending grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_member_pending.xml';
        $content = $this->_view->getLayout()->createBlock(
            'MW\Affiliate\Block\Adminhtml\Affiliatememberpending\Grid'
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
}
