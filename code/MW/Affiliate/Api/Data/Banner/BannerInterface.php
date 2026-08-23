<?php

namespace MW\Affiliate\Api\Data\Banner;

/**
 * Interface BannerInterface
 * @package MW\Affiliate\Api\Data\Banner
 */
interface BannerInterface
{

    const BANNER_ID = "banner_id";
    const TITLE_BANNER = 'title_banner';
    const LINK_BANNER = 'link_banner';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const IMAGE_NAME = 'image_name';
    const GROUP_ID = 'group_id';
    const STORE_VIEW = 'store_view';
    const STATUS = 'status';

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getBannerId();

    /**
     * @api
     * @param int $id
     * @return BannerInterface
     */
    public function setBannerId($id);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getTitleBanner();

    /**
     * @api
     * @param string $titleBanner
     * @return BannerInterface
     */
    public function setTitleBanner($titleBanner);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getLinkBanner();

    /**
     * @api
     * @param string $linkBanner
     * @return BannerInterface
     */
    public function setLinkBanner($linkBanner);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getWidth();

    /**
     * @api
     * @param int $width
     * @return BannerInterface
     */
    public function setWidth($width);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getHeight();

    /**
     * @api
     * @param int $height
     * @return BannerInterface
     */
    public function setHeight($height);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getImageName();

    /**
     * @api
     * @param string $imageName
     * @return BannerInterface
     */
    public function setImageName($imageName);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getGroupId();

    /**
     * @api
     * @param string $groupId
     * @return BannerInterface
     */
    public function setGroupId($groupId);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getStoreView();

    /**
     * @api
     * @param string $storeView
     * @return BannerInterface
     */
    public function setStoreView($storeView);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getStatus();

    /**
     * @api
     * @param int $status
     * @return BannerInterface
     */
    public function setStatus($status);
}
