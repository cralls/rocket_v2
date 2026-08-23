<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as Directory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Driver\File;

class Downloader extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Filesystem\Directory
     */
    protected $_directoryList;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Webkul\Rmasystem\Model\AllrmaFactory
     */
    protected $rmaFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Rmasystem\Model\AllrmaFactory $rmaFactory,
        \Webkul\Rmasystem\Helper\Data $helper,
        DirectoryList $directoryList,
        FileFactory $fileFactory,
        File $file
    ) {
        $this->_directoryList = $directoryList;
        $this->file = $file;
        $this->_customerSession = $customerSession;
        $this->_fileFactory = $fileFactory;
        $this->rmaFactory = $rmaFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * doenload requested file.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $rmaId = $this->getRequest()->getParam('id');
        $fileName = $this->getRequest()->getParam('file_name');
        $filePath = $this->helper->getBaseDir($rmaId);
        if ($this->getRequest()->getParam('conv')) {
            $filePath = $this->helper->getConversationDir($rmaId);
        }
        if (! $this->file->isExists($filePath.$fileName)) {
            $this->messageManager->addError(
                __('File not found to download.')
            );
             return $resultRedirect->setPath('*/*/', ['id' => $rmaId]);
        }
        return $this->_fileFactory->create(
            $fileName,
            $this->file->fileGetContents($filePath.$fileName),
            Directory::MEDIA,
            'image/jpeg'
        );
    }
}
