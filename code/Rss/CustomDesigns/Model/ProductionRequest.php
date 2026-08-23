<?php
namespace Rss\CustomDesigns\Model;

use Magento\Framework\Model\AbstractModel;

class ProductionRequest extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(
            \Rss\CustomDesigns\Model\ResourceModel\ProductionRequest::class
        );
    }
}
