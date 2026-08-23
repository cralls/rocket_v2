<?php

namespace MW\Affiliate\Model;

class Credithistory extends \Magento\Framework\Model\AbstractModel implements \MW\Affiliate\Api\Data\Transaction\TransactionInterface
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Affiliate\Model\ResourceModel\Credithistory');
    }

    public function getCreditHistoryId()
    {
        return $this->getData(self::CREDIT_HISTORY_ID);
    }
    public function setCreditHistoryId($id)
    {
        return $this->setData(self::CREDIT_HISTORY_ID, $id);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getTypeTransaction()
    {
        return $this->getData(self::TYPE_TRANSACTION);
    }
    public function setTypeTransaction($typeTransaction)
    {
        return $this->setData(self::TYPE_TRANSACTION, $typeTransaction);
    }

    public function getTransactionDetail()
    {
        return $this->getData(self::TRANSACTION_DETAIL);
    }
    public function setTransactionDetail($transactionDetail)
    {
        return $this->setData(self::TRANSACTION_DETAIL, $transactionDetail);
    }

    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    public function getBeginningTransaction()
    {
        return $this->getData(self::BEGINNING_TRANSACTION);
    }
    public function setBeginningTransaction($beginningTransaction)
    {
        return $this->setData(self::BEGINNING_TRANSACTION, $beginningTransaction);
    }

    public function getEndTransaction()
    {
        return $this->getData(self::END_TRANSACTION);
    }
    public function setEndTransaction($endTransaction)
    {
        return $this->setData(self::END_TRANSACTION, $endTransaction);
    }

    public function getCreatedTime()
    {
        return $this->getData(self::CREATED_TIME);
    }
    public function setCreatedTime($createdTime)
    {
        return $this->setData(self::CREATED_TIME, $createdTime);
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
