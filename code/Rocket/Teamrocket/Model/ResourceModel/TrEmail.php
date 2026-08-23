<?php

namespace Rocket\Teamrocket\Model\ResourceModel;

class TrEmail extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_TR_EMAIL = 'tr_email';

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_TR_EMAIL, 'id');
    }
}