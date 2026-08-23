<?php
namespace VNS\Events\Model;

use Magento\Framework\Model\AbstractModel;

class Event extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\VNS\Events\Model\ResourceModel\Event::class);
    }
}
