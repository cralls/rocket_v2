<?php
/**
 * Uploader
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Averun\SizeChart\Helper\FileProcessor;

class Uploader extends Action
{
    /**
     * @var array
     */
    protected $fixedFilesArray;

    /**
     * @var FileProcessor
     */
    protected $fileProcessor;

    /**
     * @param Context $context
     * @param FileProcessor $fileProcessor
     */
    public function __construct(Context $context, FileProcessor $fileProcessor)
    {
        parent::__construct($context);
        $this->fileProcessor = $fileProcessor;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $files = $this->getRequest()->getFiles();
        $result = $this->fileProcessor->saveToTmp(key($files));
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
