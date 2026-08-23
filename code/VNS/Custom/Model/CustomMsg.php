<?php
namespace VNS\Custom\Model;

use Magento\Framework\Model\AbstractModel;
use VNS\Custom\Model\ResourceModel\CustomMsg as CustomMsgResource;

class CustomMsg extends AbstractModel
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
    
    /**
     * Get the custom_msg table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->getResource()->getMainTable();
    }
}
