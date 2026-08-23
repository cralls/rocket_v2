<?php
namespace VNS\Custom\Block;
class Greviews extends \Magento\Framework\View\Element\Template
{
        protected $_registry;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Catalog\Model\Product $product,
        array $data = []
    )
    {
        $this->product = $product;
        $this->checkoutSession = $checkoutSession;
        $this->countryInformation = $countryInformation;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    
    public function getOrderId() {
        return $this->checkoutSession->getLastRealOrder()->getIncrementId();
    }
    
    public function getCustomerEmail() {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }
    
    public function getOrderCountryCode() {
        $countryData = $this->countryInformation->getCountryInfo($this->checkoutSession->getLastRealOrder()->getShippingAddress()->getCountryId());
        return $countryData->getTwoLetterAbbreviation();
    }
    
    public function getProducts() {
        $products = $this->checkoutSession->getLastRealOrder()->getAllItems();
        $output = array();
        foreach($products as $product) {
            $item = $this->product->load($product->getProductId());
            $output[] = '{"gtin":"'.$item->getUpc().'"}';
        }
        return implode(", ", $output);
    }
}
?>