<?php

namespace MageArray\OrderAttachments\Model\ResourceModel\FileAttachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \MageArray\OrderAttachments\Model\FileAttachment::Class,
            \MageArray\OrderAttachments\Model\ResourceModel\FileAttachment::Class
        );
    }
}
