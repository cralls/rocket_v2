<?php

namespace MW\Affiliate\Model;

class Affiliategroupprogram extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliategroupprogram');
    }
}
