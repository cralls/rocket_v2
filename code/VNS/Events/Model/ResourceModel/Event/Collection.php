<?php
namespace VNS\Events\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \VNS\Events\Model\Event::class,
            \VNS\Events\Model\ResourceModel\Event::class
        );
    }
}
