<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

use Magento\Framework\UrlInterface;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \MW\Affiliate\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MW\Affiliate\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MW\Affiliate\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['image_name'])) {
            return '';
        }

        $imageName = $row['image_name'];
        $bannerExtension = substr($imageName, strrpos($imageName, '.') + 1);
        if ($bannerExtension == 'swf') {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
            return  '<object type="application/x-shockwave-flash" data="'.$mediaUrl.$imageName.'" width="60" height="60">'
            . '<param name="wmode" value="transparent" />'
            . '<param name="movie" value="'.$mediaUrl.$imageName.'" />'
            . '</object>';
        } else {
            return '<img src="'.$this->_imageHelper->init($imageName)->resize(60, 60).'" />';
        }
    }
}
