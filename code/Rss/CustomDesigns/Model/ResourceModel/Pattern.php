<?php
namespace Rss\CustomDesigns\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Pattern extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('rss_custom_design_patterns', 'pattern_id');
    }
}
