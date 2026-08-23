<?php

namespace MW\Affiliate\Model;

class Creditorder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\Affiliate\Model\ResourceModel\Creditorder');
    }

    public function saveCreditOrder($orderData)
    {
        //$connection = $this->_getResource()->getConnection();
        //$tableName = $connection->getTableName('mw_credit_order');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('mw_credit_order');

        $sql = 'INSERT INTO '.$tableName.' (order_id,credit,affiliate)
    				VALUES('."'".$orderData['order_id']."'".','.$orderData['credit'].','.$orderData['affiliate'].')';
        $connection->query($sql);
    }
}
