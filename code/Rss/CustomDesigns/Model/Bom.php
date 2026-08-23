<?php

namespace Rss\CustomDesigns\Model;

use Magento\Framework\Model\AbstractModel;

class Bom extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Rss\CustomDesigns\Model\ResourceModel\Bom::class);
    }
}