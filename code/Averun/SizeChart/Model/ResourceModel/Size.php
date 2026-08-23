<?php

namespace Averun\SizeChart\Model\ResourceModel;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Size extends AbstractDb
{

    public function _construct()
    {
        $this->_init(EntityTypeInterface::SIZE_CODE, 'entity_id');
    }
}
