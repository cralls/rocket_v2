<?php

namespace MW\Affiliate\Model;

class Affiliatebanner extends \Magento\Framework\Model\AbstractModel implements \MW\Affiliate\Api\Data\Banner\BannerInterface
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliatebanner');
    }

    public function getBannerId()
    {
        return $this->getData(self::BANNER_ID);
    }
    public function setBannerId($id)
    {
        return $this->setData(self::BANNER_ID, $id);
    }

    public function getTitleBanner()
    {
        return $this->getData(self::TITLE_BANNER);
    }
    public function setTitleBanner($titleBanner)
    {
        return $this->setData(self::TITLE_BANNER, $titleBanner);
    }

    public function getLinkBanner()
    {
        return $this->getData(self::LINK_BANNER);
    }
    public function setLinkBanner($linkBanner)
    {
        return $this->setData(self::LINK_BANNER, $linkBanner);
    }

    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }
    public function setWidth($width)
    {
        return $this->setData(self::WIDTH, $width);
    }

    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }
    public function setHeight($height)
    {
        return $this->setData(self::HEIGHT, $height);
    }

    public function getImageName()
    {
        return $this->getData(self::IMAGE_NAME);
    }
    public function setImageName($imageName)
    {
        return $this->setData(self::IMAGE_NAME, $imageName);
    }

    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    public function getStoreView()
    {
        return $this->getData(self::STORE_VIEW);
    }
    public function setStoreView($storeView)
    {
        return $this->setData(self::STORE_VIEW, $storeView);
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
