<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliatehistory\Report;

use MW\Affiliate\Model\Status;

class Collection extends \MW\Affiliate\Model\ResourceModel\Affiliatehistory\Collection
{
    /**
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->addFieldToFilter(
            'main_table.transaction_time',
            [
                'from' => $fromDate,
                'to' => $toDate,
                'datetime' => true
            ]
        );

        $this->addFieldToFilter('status', Status::COMPLETE);

        $this->addExpressionFieldToSelect('product_id_count', 'count(product_id)', 'product_id_count');
        $this->addExpressionFieldToSelect('customer_id_count', 'count( distinct customer_id)', 'customer_id_count');
        $this->addExpressionFieldToSelect('order_id_count', 'count( distinct order_id)', 'order_id_count');
        $this->addExpressionFieldToSelect('total_amount_sum', 'sum(total_amount)', 'total_amount_sum');
        $this->addExpressionFieldToSelect('history_commission_sum', 'sum(history_commission)', 'history_commission_sum');
        $this->addExpressionFieldToSelect('history_discount_sum', 'sum(history_discount)', 'history_discount_sum');

        $this->getSelect()->group(['customer_invited']);

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
