<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliatetransaction;

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
            'MW\Affiliate\Model\Affiliatetransaction',
            'MW\Affiliate\Model\ResourceModel\Affiliatetransaction'
        );
    }
}
