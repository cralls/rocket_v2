<?php

namespace MW\Affiliate\Model;

class Creditcustomer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MW\Affiliate\Model\ResourceModel\Creditcustomer');
    }

    public function saveCreditCustomer($customerData)
    {
        //$connection = $this->_getResource()->getConnection();
        //$tableName = $connection->getTableName('mw_credit_customer');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('mw_credit_customer');
        //Insert Data into table
        $sql = 'INSERT INTO '.$tableName.'
    				VALUES('.$customerData['customer_id'].','.$customerData['credit'].')';
        $connection->query($sql);
    }
}
