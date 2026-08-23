<?php
namespace Magenest\Xero\Model\ResourceModel\TaxMapping;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magenest\Xero\Model\TaxMapping::class, \Magenest\Xero\Model\ResourceModel\TaxMapping::class);
    }
}
