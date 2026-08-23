<?php

namespace MW\Affiliate\Model\ResourceModel;

class Creditcustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_credit_customer', 'customer_id');
    }
}
