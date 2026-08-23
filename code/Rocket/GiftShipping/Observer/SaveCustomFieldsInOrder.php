<?php
namespace Rocket\GiftShipping\Observer;
 
class SaveCustomFieldsInOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
 
        $order->setData('shipping_email', $quote->getShippingEmail());
        $order->setData('shipping_gift', $quote->getShippingGift());
 
        return $this;
    }
}