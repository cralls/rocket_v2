<?php
namespace Rss\CustomDesigns\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductionRequest extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            'rss_production_requests', // table name
            'entity_id'                // primary key
        );
    }
}
