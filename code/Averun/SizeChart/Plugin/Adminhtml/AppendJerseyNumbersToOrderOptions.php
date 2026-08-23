<?php
/**
 * VNS revision 2026-05-24b: hide jersey-number order options unless parent configurable product show_numbers = 1.
 */

namespace Averun\SizeChart\Plugin\Adminhtml;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class AppendJerseyNumbersToOrderOptions
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function afterGetOrderOptions(DefaultColumn $subject, array $result): array
    {
        $item = $subject->getItem();

        if (!$item) {
            return $this->removeJerseyNumbersOptions($result);
        }

        $productOptions = $item->getProductOptions();

        if (!is_array($productOptions)) {
            return $this->removeJerseyNumbersOptions($result);
        }

        /*
         * Magento core already displays values saved in additional_options.
         * So first remove any jersey-number rows from the option list, then add them
         * back only when show_numbers is enabled on the parent/configurable product.
         */
        $result = $this->removeJerseyNumbersOptions($result);

        if (!$this->isShowNumbersEnabled($item, $productOptions)) {
            return $result;
        }

        $jerseyNumbers = $this->extractJerseyNumbers($productOptions);

        if ($jerseyNumbers === '') {
            return $result;
        }

        $result[] = [
            'label' => 'Jersey Numbers',
            'value' => $jerseyNumbers,
        ];

        return $result;
    }

    private function isShowNumbersEnabled($item, array $productOptions): bool
    {
        $productId = 0;
        $buyRequest = $productOptions['info_buyRequest'] ?? [];

        if (is_array($buyRequest) && !empty($buyRequest['product'])) {
            $productId = (int)$buyRequest['product'];
        }

        if (!$productId && $item->getParentItem() && $item->getParentItem()->getProductId()) {
            $productId = (int)$item->getParentItem()->getProductId();
        }

        if (!$productId && $item->getProductId()) {
            $productId = (int)$item->getProductId();
        }

        if (!$productId) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId, false, null, true);
        } catch (NoSuchEntityException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }

        $showNumbersValue = $product->getData('show_numbers');

        return (string)$showNumbersValue === '1' || $showNumbersValue === 1 || $showNumbersValue === true;
    }

    private function extractJerseyNumbers(array $productOptions): string
    {
        $rows = [];

        if (isset($productOptions['additional_options']) && is_array($productOptions['additional_options'])) {
            foreach ($productOptions['additional_options'] as $option) {
                if (!is_array($option) || empty($option['label']) || !isset($option['value'])) {
                    continue;
                }

                $label = trim((string)$option['label']);

                if (!$this->isJerseyNumbersLabel($label)) {
                    continue;
                }

                $numbers = $this->cleanNumberList((string)$option['value']);

                if ($numbers === '') {
                    continue;
                }

                if (stripos($label, 'Jersey Numbers - ') === 0) {
                    $size = trim(substr($label, strlen('Jersey Numbers - ')));
                    $rows[] = $size !== '' ? $size . ': ' . $numbers : $numbers;
                } else {
                    $rows[] = $numbers;
                }
            }
        }

        if (!empty($rows)) {
            return implode('; ', $rows);
        }

        $buyRequest = $productOptions['info_buyRequest'] ?? [];

        if (!is_array($buyRequest)) {
            return '';
        }

        if (isset($buyRequest['jersey_numbers'])) {
            return $this->cleanNumberList((string)$buyRequest['jersey_numbers']);
        }

        if (isset($buyRequest['ave_jersey_numbers']) && is_array($buyRequest['ave_jersey_numbers'])) {
            foreach ($buyRequest['ave_jersey_numbers'] as $size => $numbers) {
                $size = trim(strip_tags((string)$size));
                $numbers = $this->cleanNumberList((string)$numbers);

                if ($size !== '' && $numbers !== '') {
                    $rows[] = $size . ': ' . $numbers;
                }
            }
        }

        return implode('; ', $rows);
    }

    private function removeJerseyNumbersOptions(array $options): array
    {
        $filteredOptions = [];

        foreach ($options as $option) {
            if (is_array($option) && isset($option['label']) && $this->isJerseyNumbersLabel((string)$option['label'])) {
                continue;
            }

            $filteredOptions[] = $option;
        }

        return $filteredOptions;
    }

    private function isJerseyNumbersLabel(string $label): bool
    {
        $label = strtolower(trim($label));

        return $label === 'jersey numbers' || strpos($label, 'jersey numbers -') === 0 || strpos($label, 'jersey numbers') === 0;
    }

    private function cleanNumberList(string $numbers): string
    {
        $numbers = preg_replace('/[^0-9,\s]/', '', $numbers);
        $parts = preg_split('/,/', (string)$numbers);
        $cleanParts = [];

        foreach ($parts as $part) {
            $part = preg_replace('/\s+/', '', (string)$part);

            if ($part !== '') {
                $cleanParts[] = $part;
            }
        }

        return implode(', ', $cleanParts);
    }
}
