<?php
/**
 * RSS Rev2, 2026-11-01: honor external media for parent and variation galleries.
 * Intercept gallery collections, including Magento versions using Image helpers.
 */
namespace VNS\Custom\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\UrlInterface;

class DevProductMediaUrl
{
    /** @var Http */
    private $request;

    public function __construct(Http $request)
    {
        $this->request = $request;
    }

    public function afterGetGalleryImages($subject, $images, $product = null)
    {
        if (!$images instanceof Collection) {
            return $images;
        }
        $product = $product ?: $subject->getProduct();
        foreach ($images as $image) {
            $url = $this->getExternalImageUrl((string)$image->getFile(), $product);
            if ($url !== null) {
                foreach (['small_image_url', 'medium_image_url', 'large_image_url'] as $key) {
                    $image->setData($key, $url);
                }
            }
        }
        return $images;
    }

    public function afterGetProductMediaGallery($subject, $result, $product)
    {
        if (empty($result) || !is_array($result)) {
            return $result;
        }
        $mainImageId = null;
        foreach ((array)$product->getMediaGalleryEntries() as $entry) {
            if ($entry->isDisabled()) {
                continue;
            }
            $id = $entry->getId();
            if ($mainImageId === null || in_array('image', (array)$entry->getTypes(), true)) {
                $mainImageId = $id;
            }
            $url = $this->getExternalImageUrl((string)$entry->getFile(), $product);
            if ($url !== null && isset($result['gallery'][$id])) {
                foreach (['small', 'medium', 'large'] as $key) {
                    $result['gallery'][$id][$key] = $url;
                }
            }
        }
        if ($mainImageId !== null && isset($result['gallery'][$mainImageId])) {
            foreach (['small', 'medium', 'large'] as $key) {
                if (isset($result['gallery'][$mainImageId][$key])) {
                    $result[$key] = $result['gallery'][$mainImageId][$key];
                }
            }
        }
        return $result;
    }

    private function getExternalImageUrl(string $file, $product): ?string
    {
        $mediaUrl = rtrim($product->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true), '/') . '/';
        $mediaHost = strtolower((string)parse_url($mediaUrl, PHP_URL_HOST));
        $requestHost = strtolower((string)parse_url('https://' . $this->request->getHttpHost(), PHP_URL_HOST));
        $file = ltrim($file, '/');
        if ($mediaHost === '' || $mediaHost === $requestHost || $file === '' || $file === 'no_selection'
            || strpos($file, '://') !== false || strpos($file, "\0") !== false
            || in_array('..', explode('/', $file), true)
        ) {
            return null;
        }
        return $mediaUrl . 'catalog/product/' . implode('/', array_map('rawurlencode', explode('/', $file)));
    }
}
