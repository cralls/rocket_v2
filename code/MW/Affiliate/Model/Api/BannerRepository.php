<?php

namespace MW\Affiliate\Model\Api;

/**
 * Class BannerRepository
 * @package MW\Affiliate\Model\Api
 */
class BannerRepository implements \MW\Affiliate\Api\Banner\BannerRepositoryInterface
{

    /**
     * @var  \MW\Affiliate\Model\ResourceModel\Affiliatebanner
     */
    protected $bannerResourceModel;
    /**
     * @var \MW\Affiliate\Model\ResourceModel\Affiliatebanner\CollectionFactory
     */
    protected $bannerResourceCollection;
    /**
     * @var \MW\Affiliate\Api\Data\Banner\BannerInterfaceFactory
     */
    protected $bannerFactory;

    public function __construct(
        \MW\Affiliate\Model\ResourceModel\Affiliatebanner $bannerResourceModel,
        \MW\Affiliate\Model\ResourceModel\Affiliatebanner\CollectionFactory $bannerResourceCollection,
        \MW\Affiliate\Api\Data\Banner\BannerInterfaceFactory $bannerFactory
    ) {
        $this->bannerResourceModel = $bannerResourceModel;
        $this->bannerResourceCollection = $bannerResourceCollection;
        $this->bannerFactory = $bannerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MW\Affiliate\Api\Data\Banner\BannerInterface $banner)
    {
        try {
            $this->bannerResourceModel->save($banner);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Banner'));
        }
        return $banner;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($bannerId)
    {
        $banner = $this->bannerFactory->create();
        $this->bannerResourceModel->load($banner, $bannerId);
        if (!$banner->getBannerId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Banner with id "%1" does not exist.', $bannerId));
        } else {
            return $banner;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MW\Affiliate\Api\Data\Banner\BannerInterface $banner)
    {
        return $this->deleteById($banner->getBannerId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($bannerId)
    {
        $banner = $this->getById($bannerId);
        $this->bannerResourceModel->delete($banner);
    }
}
