<?php

namespace MW\Affiliate\Model\ResourceModel;

class Creditorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_credit_order', 'order_id');
    }
}
