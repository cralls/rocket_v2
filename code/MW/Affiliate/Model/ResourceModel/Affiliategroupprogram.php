<?php

namespace MW\Affiliate\Model\ResourceModel;

class Affiliategroupprogram extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_affiliate_group_program', 'id');
    }
}
