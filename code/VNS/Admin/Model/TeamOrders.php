<?php

namespace VNS\Admin\Model;

use Magento\Framework\Model\AbstractModel;
use VNS\Admin\Model\ResourceModel\TeamOrders as ResourceModel;

class TeamOrders extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}