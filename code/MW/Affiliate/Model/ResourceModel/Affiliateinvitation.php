<?php

namespace MW\Affiliate\Model\ResourceModel;

class Affiliateinvitation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_affiliate_invitation', 'invitation_id');
    }
}
