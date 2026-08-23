<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliateprogram;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MW\Affiliate\Model\Affiliateprogram',
            'MW\Affiliate\Model\ResourceModel\Affiliateprogram'
        );
    }
}
