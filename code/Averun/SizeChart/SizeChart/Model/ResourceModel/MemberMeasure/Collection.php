<?php
namespace Averun\SizeChart\Model\ResourceModel\MemberMeasure;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\MemberMeasure', 'Averun\SizeChart\Model\ResourceModel\MemberMeasure');
    }
}
