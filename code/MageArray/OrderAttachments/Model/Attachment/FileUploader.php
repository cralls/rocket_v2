<?php
namespace MageArray\OrderAttachments\Model\Attachment;

use MageArray\OrderAttachments\Helper\File;
use Magento\Framework\File\Uploader;

class FileUploader extends Uploader
{
    protected $_allowRenameFiles = false;

    protected $_enableFilesDispersion = true;

    protected $_allowedExtensions = null;

    protected $fileHelper;

    /**
     * FileUploader constructor.
     * @param string|array $fileId
     * @param File $fileHelper
     */
    public function __construct(
        $fileId,
        File $fileHelper
    ) {
        parent::__construct($fileId);
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param $result
     * @return mixed
     */
    protected function _afterSave($result)
    {
        $this->_result['text_file_size'] = $this->fileHelper
            ->getTextFileSize($this->_file['size']);
        return parent::_afterSave($result);
    }
}
