<?php

namespace MW\Affiliate\Api\Account;

interface AccountRepositoryInterface
{
    /**
     * Save User.
     *
     * @param \MW\Affiliate\Api\Data\Account\AccountInterface $account
     * @return \MW\Affiliate\Api\Data\Account\AccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MW\Affiliate\Api\Data\Account\AccountInterface $account);

    /**
     * Retrieve User.
     *
     * @param int $accountId
     * @return \MW\Affiliate\Api\Data\Account\AccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($accountId);

    /**
     * Delete user.
     *
     * @param \MW\Affiliate\Api\Data\Account\AccountInterface $account
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MW\Affiliate\Api\Data\Account\AccountInterface $account);

    /**
     * Delete user by ID.
     *
     * @param int $accountId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($accountId);
}
