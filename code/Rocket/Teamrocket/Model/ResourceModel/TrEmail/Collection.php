<?php

namespace Rocket\Teamrocket\Model\ResourceModel\TrEmail;

/**
 * Class Collection
 * @package VNS\Admin\Model\ResourceModel\PowdersheetItem
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Rocket\Teamrocket\Model\TrEmail',
            'Rocket\Teamrocket\Model\ResourceModel\TrEmail');
    }
}