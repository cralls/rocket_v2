<?php

namespace MW\Affiliate\Api\Banner;

interface BannerRepositoryInterface
{
    /**
     * @param \MW\Affiliate\Api\Data\Banner\BannerInterface $banner
     * @return \MW\Affiliate\Api\Data\Banner\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MW\Affiliate\Api\Data\Banner\BannerInterface $banner);

    /**
     * @param int $bannerId
     * @return \MW\Affiliate\Api\Data\Banner\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($bannerId);

    /**
     * @param \MW\Affiliate\Api\Data\Banner\BannerInterface $banner
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MW\Affiliate\Api\Data\Banner\BannerInterface $banner);

    /**
     * @param int $bannerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bannerId);
}
