<?php
/**
 * RSS Rev1, 2026-11-01: dev PDP galleries read original images from live media.
 * Covers the initial gallery, configurable selections and swatch AJAX galleries.
 */
namespace VNS\Custom\Plugin;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Request\Http;

class DevProductMediaUrl
{
    private const PRODUCT_MEDIA_URL = 'https://www.rocketsciencesports.com/media/catalog/product/';

    private const GALLERY_IMAGE_IDS = [
        'product_page_image_small',
        'product_page_image_medium',
        'product_page_image_large',
        'product_swatch_image_small',
        'product_swatch_image_medium',
        'product_swatch_image_large'
    ];

    /** @var Http */
    private $request;

    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    public function aroundGetUrl(
        UrlBuilder $subject,
        callable $proceed,
        string $baseFilePath,
        string $imageDisplayArea
    ): string {
        if (!preg_match('/^dev\.rocketsciencesports\.com(?::\d+)?$/i', (string)$this->request->getHttpHost())
            || !in_array($imageDisplayArea, self::GALLERY_IMAGE_IDS, true)
        ) {
            return $proceed($baseFilePath, $imageDisplayArea);
        }

        $file = ltrim($baseFilePath, '/');
        if ($file === '' || $file === 'no_selection'
            || strpos($file, '://') !== false
            || strpos($file, "\0") !== false
            || in_array('..', explode('/', $file), true)
        ) {
            return $proceed($baseFilePath, $imageDisplayArea);
        }

        // Original files avoid dependence on cache hashes generated on dev.
        return self::PRODUCT_MEDIA_URL . implode('/', array_map('rawurlencode', explode('/', $file)));
    }
}
