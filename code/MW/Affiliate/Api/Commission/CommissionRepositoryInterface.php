<?php

namespace MW\Affiliate\Api\Commission;

interface CommissionRepositoryInterface
{
    /**
     * @param \MW\Affiliate\Api\Data\Commission\CommissionInterface $commission
     * @return \MW\Affiliate\Api\Data\Commission\CommissionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MW\Affiliate\Api\Data\Commission\CommissionInterface $commission);

    /**
     * @param int $commissionId
     * @return \MW\Affiliate\Api\Data\Commission\CommissionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($commissionId);

    /**
     * @param \MW\Affiliate\Api\Data\Commission\CommissionInterface $commission
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MW\Affiliate\Api\Data\Commission\CommissionInterface $commission);

    /**
     * @param int $commissionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($commissionId);
}
