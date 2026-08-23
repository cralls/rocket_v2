<?php

namespace MW\Affiliate\Model;

class Affiliatetransaction extends \Magento\Framework\Model\AbstractModel implements \MW\Affiliate\Api\Data\Commission\CommissionInterface
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliatetransaction');
    }

    public function getHistoryId()
    {
        return $this->getData(self::HISTORY_ID);
    }
    public function setHistoryId($id)
    {
        return $this->setData(self::HISTORY_ID, $id);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getTotalCommission()
    {
        return $this->getData(self::TOTAL_COMMISSION);
    }
    public function setTotalCommission($totalCommission)
    {
        return $this->setData(self::TOTAL_COMMISSION, $totalCommission);
    }

    public function getTotalDiscount()
    {
        return $this->getData(self::TOTAL_DISCOUNT);
    }
    public function setTotalDiscount($totalDiscount)
    {
        return $this->setData(self::TOTAL_DISCOUNT, $totalDiscount);
    }

    public function getTransactionTime()
    {
        return $this->getData(self::TRANSACTION_TIME);
    }
    public function setTransactionTime($transactionTime)
    {
        return $this->setData(self::TRANSACTION_TIME, $transactionTime);
    }

    public function getCommissionType()
    {
        return $this->getData(self::COMMISSION_TYPE);
    }
    public function setCommissionType($commissionType)
    {
        return $this->setData(self::COMMISSION_TYPE, $commissionType);
    }

    public function getShowCustomerInvited()
    {
        return $this->getData(self::SHOW_CUSTOMER_INVITED);
    }
    public function setShowCustomerInvited($show_customer_invited)
    {
        return $this->setData(self::SHOW_CUSTOMER_INVITED, $show_customer_invited);
    }

    public function getCustomerInvited()
    {
        return $this->getData(self::CUSTOMER_INVITED);
    }
    public function setCustomerInvited($customerInvited)
    {
        return $this->setData(self::CUSTOMER_INVITED, $customerInvited);
    }

    public function getInvitationType()
    {
        return $this->getData(self::INVITATION_TYPE);
    }
    public function setInvitationType($invitationType)
    {
        return $this->setData(self::INVITATION_TYPE, $invitationType);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
