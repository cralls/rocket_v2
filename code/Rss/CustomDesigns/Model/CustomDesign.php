<?php
namespace Rss\CustomDesigns\Model;

use Magento\Framework\Model\AbstractModel;
use Rss\CustomDesigns\Model\ResourceModel\CustomDesign as ResourceModelCustomDesign;

class CustomDesign extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModelCustomDesign::class);
    }
}
