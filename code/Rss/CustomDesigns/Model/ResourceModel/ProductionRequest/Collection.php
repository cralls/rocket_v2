<?php
namespace Rss\CustomDesigns\Model\ResourceModel\ProductionRequest;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Rss\CustomDesigns\Model\ProductionRequest;
use Rss\CustomDesigns\Model\ResourceModel\ProductionRequest as ResourceModel;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ProductionRequest::class,
            ResourceModel::class
        );
    }
}
