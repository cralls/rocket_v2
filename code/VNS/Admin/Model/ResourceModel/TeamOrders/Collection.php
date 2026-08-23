<?php

namespace VNS\Admin\Model\ResourceModel\TeamOrders;

/**
 * Class Collection
 * @package VNS\Admin\Model\ResourceModel\TeamOrders
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\VNS\Admin\Model\TeamOrders::class, \VNS\Admin\Model\ResourceModel\TeamOrders::class);
        parent::_construct();
    }
}