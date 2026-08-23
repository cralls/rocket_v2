<?php

namespace Rss\CustomDesigns\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Bom extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('rss_custom_designs_bom', 'material_id');
    }
}