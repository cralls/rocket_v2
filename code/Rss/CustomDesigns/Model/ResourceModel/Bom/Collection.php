<?php

namespace Rss\CustomDesigns\Model\ResourceModel\Bom;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Rss\CustomDesigns\Model\Bom::class,
            \Rss\CustomDesigns\Model\ResourceModel\Bom::class
        );
    }
}