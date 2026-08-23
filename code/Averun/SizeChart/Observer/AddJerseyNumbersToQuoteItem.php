<?php
/**
 * VNS revision 2026-05-24: only collect jersey numbers when parent configurable product attribute show_numbers = 1.
 */

namespace Averun\SizeChart\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class AddJerseyNumbersToQuoteItem implements ObserverInterface
{
    private RequestInterface $request;

    private Json $json;

    private ProductRepositoryInterface $productRepository;

    public function __construct(
        RequestInterface $request,
        Json $json,
        ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->json = $json;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer): void
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();

        if (!$quoteItem) {
            return;
        }

        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }

        if (!$this->isShowNumbersEnabled($quoteItem)) {
            return;
        }

        $postedNumbers = $this->request->getParam('ave_jersey_numbers', []);

        if (!is_array($postedNumbers) || empty($postedNumbers)) {
            return;
        }

        $cleanRows = [];

        foreach ($postedNumbers as $size => $numbers) {
            $size = trim(strip_tags((string)$size));
            $numbers = trim((string)$numbers);

            if ($size === '' || $numbers === '') {
                continue;
            }

            $numbers = preg_replace('/[^0-9,\s]/', '', $numbers);
            $numberParts = preg_split('/,/', $numbers);
            $cleanNumberParts = [];

            foreach ($numberParts as $numberPart) {
                $numberPart = preg_replace('/\s+/', '', (string)$numberPart);

                if ($numberPart !== '') {
                    $cleanNumberParts[] = $numberPart;
                }
            }

            if (empty($cleanNumberParts)) {
                continue;
            }

            $cleanRows[] = [
                'label' => 'Jersey Numbers - ' . $size,
                'value' => implode(', ', $cleanNumberParts),
            ];
        }

        if (empty($cleanRows)) {
            return;
        }

        $existingOptions = [];
        $existingOption = $quoteItem->getOptionByCode('additional_options');

        if ($existingOption && $existingOption->getValue()) {
            $unserializedOptions = $this->json->unserialize($existingOption->getValue());

            if (is_array($unserializedOptions)) {
                $existingOptions = $unserializedOptions;
            }
        }

        $quoteItem->addOption([
            'code' => 'additional_options',
            'value' => $this->json->serialize(array_merge($existingOptions, $cleanRows)),
        ]);
    }

    private function isShowNumbersEnabled($quoteItem): bool
    {
        $productId = (int)$this->request->getParam('product');

        if (!$productId && $quoteItem->getProduct()) {
            $productId = (int)$quoteItem->getProduct()->getId();
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
}
