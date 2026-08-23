<?php

namespace Rocket\Teamrocket\Model;

use Magento\Framework\Model\AbstractModel;
use Rocket\Teamrocket\Model\ResourceModel\TrEmail as ResourceModel;

class TrEmail extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}