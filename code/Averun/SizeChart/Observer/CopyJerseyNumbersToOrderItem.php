<?php

namespace Averun\SizeChart\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;

class CopyJerseyNumbersToOrderItem implements ObserverInterface
{
    private Json $json;

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if (!$quote || !$order) {
            return;
        }

        foreach ($order->getAllItems() as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();

            if (!$quoteItemId) {
                continue;
            }

            $quoteItem = $quote->getItemById($quoteItemId);

            if (!$quoteItem) {
                continue;
            }

            $additionalOptions = $quoteItem->getOptionByCode('additional_options');

            if (!$additionalOptions || !$additionalOptions->getValue()) {
                continue;
            }

            $productOptions = $orderItem->getProductOptions() ?: [];
            $productOptions['additional_options'] = $this->json->unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($productOptions);
        }
    }
}
