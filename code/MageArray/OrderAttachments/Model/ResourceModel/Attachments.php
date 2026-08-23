<?php

namespace MageArray\OrderAttachments\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Attachments extends AbstractDb
{

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('magearray_order_attachments', 'id');
    }
}
