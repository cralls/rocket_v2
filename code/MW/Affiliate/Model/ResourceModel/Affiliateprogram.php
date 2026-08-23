<?php

namespace MW\Affiliate\Model\ResourceModel;

class Affiliateprogram extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mw_affiliate_program', 'program_id');
    }
}
