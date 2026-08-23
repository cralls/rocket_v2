<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Model\ResourceModel\Log;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Vectornetworksolutionsllc\Commercebug\Model\Log','Vectornetworksolutionsllc\Commercebug\Model\ResourceModel\Log');
    }
}
