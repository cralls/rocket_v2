<?php
namespace Averun\SizeChart\Model\ResourceModel\Member;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\Member', 'Averun\SizeChart\Model\ResourceModel\Member');
    }

    public function massUpdate(array $data)
    {
        if (empty($this->getAllIds())) {
            return $this;
        }
        $this->getConnection()->update(
            $this->getResource()->getMainTable(),
            $data,
            $this->getResource()->getIdFieldName() . ' IN(' . implode(',', $this->getAllIds()) . ')'
        );
        return $this;
    }
}
