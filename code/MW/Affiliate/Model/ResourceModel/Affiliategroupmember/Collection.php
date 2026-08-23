<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliategroupmember;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MW\Affiliate\Model\Affiliategroupmember',
            'MW\Affiliate\Model\ResourceModel\Affiliategroupmember'
        );
    }
}
