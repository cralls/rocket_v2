<?php

namespace MageArray\OrderAttachments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FileAttachment extends AbstractDb
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('magearray_file_attachments', 'id');
    }
}
