<?php

namespace MW\Affiliate\Model\ResourceModel;

class Affiliatecustomers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_affiliate_customers', 'customer_id');
        $this->_isPkAutoIncrement = false;
    }
}
