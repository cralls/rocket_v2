<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliatehistory;

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
            'MW\Affiliate\Model\Affiliatehistory',
            'MW\Affiliate\Model\ResourceModel\Affiliatehistory'
        );
    }

    /**
     * @param array|string $attribute
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        if ($attribute == 'status') {
            $attribute = 'main_table.'.$attribute;
        }

        return parent::addFieldToFilter($attribute, $condition);
    }
}
