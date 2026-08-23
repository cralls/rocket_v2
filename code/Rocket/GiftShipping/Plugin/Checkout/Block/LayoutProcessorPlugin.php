<?php
namespace Rocket\GiftShipping\Plugin\Checkout\Block;
 
class LayoutProcessorPlugin
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
  
		// Code from original extension
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['shipping_email'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'shipping-email'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.shipping_email',
            'label' => 'Shipping Email',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [
                'required-entry' => false
            ],
            'sortOrder' => 250,
            'id' => 'shipping-email'
        ];
        
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['shipping_gift'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/checkbox',
                'options' => [],
                'id' => 'shipping-gift'
            ],
            'dataScope' => 'shippingAddress.custom_attributes.shipping_gift',
            'label' => 'Is this a gift?',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => [
                'required-entry' => false
            ],
            'sortOrder' => 250,
            'id' => 'shipping-gift'
        ];
 
        return $jsLayout;
    }
}