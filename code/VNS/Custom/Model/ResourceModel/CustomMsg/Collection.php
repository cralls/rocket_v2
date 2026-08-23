<?php

namespace VNS\Custom\Model\ResourceModel\CustomMsg;

/**
 * Class Collection
 * @package VNS\Admin\Model\ResourceModel\TeamOrders
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'msg_id';
    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('\VNS\Custom\Model\CustomMsg', '\VNS\Custom\Model\ResourceModel\CustomMsg');
        parent::_construct();
    }
}