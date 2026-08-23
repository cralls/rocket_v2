<?php

namespace MW\Affiliate\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }

    /**
     * Get media directory
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Save banner image
     *
     * @param  array $imageData
     * @return string $fileName
     */
    public function saveBannerImage($imageData)
    {
        /* Starting upload */
        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
        $uploader = $this->_fileUploaderFactory->create(['fileId' => 'image_name']);
        // Any extention would work
        $uploader->setAllowedExtensions(['jpg','jpeg','gif','png','swf']);
        $uploader->setAllowRenameFiles(true);
        // Set the file upload mode
        // false -> get the file directly in the specified folder
        // true -> get the file in the product like folders
        //    (file.jpg will go in something like /media/f/i/file.jpg)
        $uploader->setFilesDispersion(false);
        $fileName = $uploader->getCorrectFileName($imageData['name']);
        // We set media as the upload dir
        $path = $this->getMediaDirectory() . '/mw_affiliate';
        $uploader->save($path, $fileName);

        return $fileName;
    }
}
