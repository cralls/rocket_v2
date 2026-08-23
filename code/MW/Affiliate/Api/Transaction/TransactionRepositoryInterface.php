<?php

namespace MW\Affiliate\Api\Transaction;

interface TransactionRepositoryInterface
{
    /**
     * @param \MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction
     * @return \MW\Affiliate\Api\Data\Transaction\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction);

    /**
     * @param int $transactionId
     * @return \MW\Affiliate\Api\Data\Transaction\TransactionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($transactionId);

    /**
     * @param \MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction);

    /**
     * @param int $transaction
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($transaction);
}
