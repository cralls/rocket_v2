<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliateinvitation\Referralsite;

use MW\Affiliate\Model\Statusinvitation;

class Collection extends \MW\Affiliate\Model\ResourceModel\Affiliateinvitation\Collection
{
    /**
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $customerTable = $this->_resource->getTable('customer_entity');

        $this->addFieldToFilter(
            'main_table.invitation_time',
            [
                'from' => $fromDate,
                'to' => $toDate,
                'datetime' => true
            ]
        );

        $this->addFieldToFilter('referral_from_domain', ['neq' => '']);

        $status = [
            Statusinvitation::CLICKLINK,
            Statusinvitation::REGISTER,
            Statusinvitation::PURCHASE
        ];
        $this->getSelect()->joinLeft(
            ['customer_entity' => $customerTable],
            'main_table.customer_id = customer_entity.entity_id',
            ['website_id']
        );

        $this->addFieldToFilter('status', ['in' => $status]);
        $this->addExpressionFieldToSelect('count_click_link_sum', 'sum(count_click_link)', 'count_click_link_sum');
        $this->addExpressionFieldToSelect('count_register_sum', 'sum(count_register)', 'count_register_sum');
        $this->addExpressionFieldToSelect('count_purchase_sum', 'sum(count_purchase)', 'count_purchase_sum');

        $this->getSelect()->group(['referral_from_domain']);

        return $this;
    }

    /**
     * Set date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset()->_joinFields($fromDate, $toDate);

        return $this;
    }

    /**
     * Set store filter collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        return $this;
    }
}
