<?php

namespace Averun\SizeChart\Controller\Adminhtml\Data;

use Averun\SizeChart\Controller\Adminhtml\Data;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

class ImportExport extends Data
{
    /**
     * Import and export Page
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Averun_SizeChart::import');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('Averun\SizeChart\Block\Adminhtml\Data\ImportExportHeader')
        );
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock('Averun\SizeChart\Block\Adminhtml\Data\ImportExport')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Averun'));
        $resultPage->getConfig()->getTitle()->prepend(__('Size Chart'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import and Export Default Size Charts'));
        return $resultPage;
    }
}
