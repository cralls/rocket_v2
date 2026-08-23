<?php

namespace MW\Affiliate\Model\ResourceModel;

class Affiliatewebsitemember extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_affiliate_website_member', 'affiliate_website_id');
    }
}
