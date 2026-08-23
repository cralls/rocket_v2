<?php

namespace MW\Affiliate\Model\ResourceModel\Affiliateinvitation;

use MW\Affiliate\Model\Statusinvitation;

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
            'MW\Affiliate\Model\Affiliateinvitation',
            'MW\Affiliate\Model\ResourceModel\Affiliateinvitation'
        );
    }

    /**
     * @param $customerId
     * @return $this
     */
    public function setReportInvitation($customerId)
    {
        $status = [
            Statusinvitation::CLICKLINK,
            Statusinvitation::REGISTER,
            Statusinvitation::SUBSCRIBE,
            Statusinvitation::PURCHASE
        ];

        $this->_reset()->addFieldToFilter('customer_id', $customerId);

        $this->addFieldToFilter('status', ['in' => $status]);
        $this->addExpressionFieldToSelect('count_click_link_sum', 'sum(count_click_link)', 'count_click_link_sum');
        $this->addExpressionFieldToSelect('count_register_sum', 'sum(count_register)', 'count_register_sum');
        $this->addExpressionFieldToSelect('count_purchase_sum', 'sum(count_purchase)', 'count_purchase_sum');
        $this->addExpressionFieldToSelect('count_subscribe_sum', 'sum(count_subscribe)', 'count_subscribe_sum');
        $this->getSelect()->group(['customer_id']);

        return $this;
    }
}
