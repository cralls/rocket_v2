<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatebanner;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportXml extends \MW\Affiliate\Controller\Adminhtml\Affiliatebanner
{
    /**
     * Export banner grid to XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_banner.xml';
        $content = $this->_view->getLayout()->createBlock(
            'MW\Affiliate\Block\Adminhtml\Affiliatebanner\Grid'
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
