<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Export affiliate member pending grid to CSV format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'affiliate_member.csv';
        $content = $this->_view->getLayout()->createBlock(
            'MW\Affiliate\Block\Adminhtml\Affiliatemember\Grid'
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
}
