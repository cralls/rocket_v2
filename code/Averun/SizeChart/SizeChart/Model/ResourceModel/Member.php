<?php

namespace Averun\SizeChart\Model\ResourceModel;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Member extends AbstractDb
{

    public function _construct()
    {
        $this->_init(EntityTypeInterface::MEMBER_CODE, 'entity_id');
    }

    public function loadByFields(AbstractModel $model, $bindFields)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from($this->getMainTable(), '*');
        foreach ($bindFields as $key => $value) {
            $select->where($key . ' = ' . $value);
        }
        $modelId = $adapter->fetchOne($select);
        if ($modelId) {
            $this->load($model, $modelId);
        } else {
            $model->setData([]);
        }
        return $this;
    }
}
