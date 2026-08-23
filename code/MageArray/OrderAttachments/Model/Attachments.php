<?php

namespace MageArray\OrderAttachments\Model;

use MageArray\OrderAttachments\Helper\Data;
use MageArray\OrderAttachments\Model\Attachments as AttachmentList;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\MediaStorage\Helper\File\Storage\Database;

class Attachments extends AbstractModel
{
    const TMP_PATH = 'tmp/magearray/attachments/';

    private $filesystem;

    /**
     * Attachments constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Filesystem $filesystem
     * @param Database $coreFileStorageDatabase
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        Database $coreFileStorageDatabase,
        Data $dataHelper,
        \Magento\Framework\Filesystem\Driver\File $reader,
        File $fileSystemIo,
        array $data = [ ]
    ) {
        parent::__construct(
            $context,
            $registry,
            null,
            null,
            $data
        );
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->_dataHelper = $dataHelper;
        $this->reader = $reader;
        $this->_fileSystemIo = $fileSystemIo;
    }

    public function _construct()
    {
        $this->_init(\MageArray\OrderAttachments\Model\ResourceModel\Attachments::Class);
    }

    // @codingStandardsIgnoreStart
    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        // @codingStandardsIgnoreEnd

        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = $mediaDirectory->getAbsolutePath(AttachmentList::TMP_PATH) . $this->getFilePath();
        if ($this->reader->isExists($fileName)) {
            $newFilePath = $this->moveFileFromTmp($this->getFilePath());
            $this->setData('file_path', $newFilePath);
        }
        return parent::beforeSave();
    }

    /**
     * @param $name
     * @return mixed
     * @throws LocalizedException
     */
    public function moveFileFromTmp($name)
    {
        $mainPath = $this->_dataHelper->getAttachmentFilePath();
        $baseTmpFilePath = AttachmentList::TMP_PATH . $name;
        if ($this->_dataHelper->getEnableCustomFile() && !empty($this->getOrderId())) {
            $customFilepath = $this->_dataHelper->getCustomFilePath($this->getOrderId());
            $name = $customFilepath . $this->getNewFileName($name);
            $name = $this->checkForFileNameExist($name);
            $baseFilePath = $mainPath . '/' . $name;
        } else {
            $name = $this->checkForFileNameExist($name);
            $baseFilePath = $mainPath . $name;
        }
        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpFilePath,
                $baseFilePath
            );
            $this->mediaDirectory->renameFile(
                $baseTmpFilePath,
                $baseFilePath
            );
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $name;
    }
    
    public function checkForFileNameExist($name)
    {
        $baseAbsPath =  $this->_dataHelper->getMediaAbsolutePath().$name;
        if ($this->reader->isExists($baseAbsPath)) {
            $random = time();
            $fileInfo = $this->_fileSystemIo->getPathInfo($name);
            $newName  = $fileInfo['filename']. "_". $random . "." . $fileInfo['extension'];
            $name = str_replace($fileInfo['basename'], $newName, $name);
        }
        
        return $name;
    }

    /**
     * @return mixed
     */
    public function getContentLength()
    {
        $contentLength = $this->getData('content_length');
        if ($contentLength === null) {
            $this->setData('content_length', strlen($this->getContent()));
        }
        return $this->getData('content_length');
    }
    public function getNewFileName($path)
    {
        $fileInfo = $this->_fileSystemIo->getPathInfo($path);
        return     $fileInfo['basename'];
    }
}
