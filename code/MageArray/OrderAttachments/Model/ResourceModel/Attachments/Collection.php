<?php

namespace MageArray\OrderAttachments\Model\ResourceModel\Attachments;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init(
            \MageArray\OrderAttachments\Model\Attachments::Class,
            \MageArray\OrderAttachments\Model\ResourceModel\Attachments::Class
        );
    }
}
