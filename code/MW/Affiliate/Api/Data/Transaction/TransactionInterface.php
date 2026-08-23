<?php

namespace MW\Affiliate\Api\Data\Transaction;

/**
 * Interface TransactionInterface
 * @package MW\Affiliate\Api\Data\Transaction
 */
interface TransactionInterface
{
    const CREDIT_HISTORY_ID = "credit_history_id";
    const CUSTOMER_ID = 'customer_id';
    const TYPE_TRANSACTION = 'type_transaction';
    const TRANSACTION_DETAIL = 'transaction_detail';
    const AMOUNT = 'amount';
    const BEGINNING_TRANSACTION = 'beginning_transaction';
    const END_TRANSACTION = 'end_transaction';
    const CREATED_TIME = 'created_time';
    const STATUS = 'status';

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getCreditHistoryId();

    /**
     * @api
     * @param int $id
     * @return TransactionInterface
     */
    public function setCreditHistoryId($id);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getCustomerId();

    /**
     * @api
     * @param int $customerId
     * @return TransactionInterface
     */
    public function setCustomerId($customerId);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getTypeTransaction();

    /**
     * @api
     * @param int $typeTransaction
     * @return TransactionInterface
     */
    public function setTypeTransaction($typeTransaction);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getTransactionDetail();

    /**
     * @api
     * @param string $transactionDetail
     * @return TransactionInterface
     */
    public function setTransactionDetail($transactionDetail);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getAmount();

    /**
     * @api
     * @param float $amount
     * @return TransactionInterface
     */
    public function setAmount($amount);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getBeginningTransaction();

    /**
     * @api
     * @param float $beginningTransaction
     * @return TransactionInterface
     */
    public function setBeginningTransaction($beginningTransaction);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getEndTransaction();

    /**
     * @api
     * @param float $endTransaction
     * @return TransactionInterface
     */
    public function setEndTransaction($endTransaction);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getCreatedTime();

    /**
     * @api
     * @param string $createdTime
     * @return TransactionInterface
     */
    public function setCreatedTime($createdTime);


    /*----------------------------------------------------------------*/


    /**
     * @api
     * @return int
     */
    public function getStatus();

    /**
     * @api
     * @param int $status
     * @return TransactionInterface
     */
    public function setStatus($status);
}
