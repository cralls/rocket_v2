<?php
namespace VNS\Custom\Plugin;

class OrderRestrictPlugin
{
    public function aroundPlaceOrder(
        \Magento\Quote\Api\CartManagementInterface $subject,
        callable $proceed,
        $cartId,
        $paymentMethod = null
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
        $address = $cart->getQuote()->getShippingAddress();
        
        // Check specific name and address
        $forbiddenName = "USjames deasa";
        $forbiddenAddress = "14814 Gale Ave,Hacienda Heights,California,91745";
        if (strpos($address->getFirstname().' '.$address->getLastname(), $forbiddenName) !== false ||
            strpos($address->format('text'), $forbiddenAddress) !== false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order cannot be placed.'));
        }
        
        // Order attempts logic here - omitted for brevity
        
        return $proceed($cartId, $paymentMethod);
    }
}
