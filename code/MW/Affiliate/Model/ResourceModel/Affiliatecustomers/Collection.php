<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliatecustomers;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MW\Affiliate\Model\Affiliatecustomers',
            'MW\Affiliate\Model\ResourceModel\Affiliatecustomers'
        );
    }

    /**
     * @param array|string $attribute
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $customerTable = $this->getTable('customer_entity');

        if ($attribute == 'customer_id') {
            $attribute = 'main_table.' . $attribute;
        } elseif ($attribute == 'referral_name') {
            $attribute = $customerTable . '.entity_id';
        } elseif ($attribute == 'status') {
            $attribute = 'main_table.' . $attribute;
        };

        return parent::addFieldToFilter($attribute, $condition);
    }
}
