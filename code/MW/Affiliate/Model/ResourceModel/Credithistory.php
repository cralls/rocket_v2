<?php

namespace MW\Affiliate\Model\ResourceModel;

class Credithistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_credit_history', 'credit_history_id');
    }
}
