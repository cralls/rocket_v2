<?php
namespace VNS\Custom\Block;

class Gtag extends \Magento\Framework\View\Element\Template
{
        protected $_registry;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    )
    {        
        $this->_registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCurrentCategory()
    {        
        return $this->_registry->registry('current_category');
    }
    
    public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }    
    
    public function getOrderIncrementId() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getIncrementId();
    }
    
    public function getRealOrderId()
    {
        $lastorderId = $this->checkoutSession->getLastOrderId();
        return $lastorderId;
    }
    
    public function getGrandTotal() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getGrandTotal();
    }
    
    public function getDiscountAmount() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getDiscountAmount();
    }
    
    public function getTaxAmount() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getTaxAmount();
    }
    
    public function getShippingAmount() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getShippingAmount();
    }
    
    public function getAllItems() {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        return $order->getAllItems();
    }
    
    public function getManufacturer($sku) {
        $product = $this->productRepository->get($sku);
        $manufacturer = $product->getResource()->getAttribute('manufacturer')->getFrontend()->getValue($product);
        return $manufacturer;
    }
    
    public function isConfigurable($sku) {
        try {
            $product = $this->productRepository->get($sku);
            return $product->getTypeId() != 'simple' ? true : false;
        } catch (\Exception $e){
            return false;
        }
    }
    
    public function getCustomerData()
    {
        $order = $this->orderFactory->create()->load($this->getRealOrderId());
        $customerData = [];
        
        // Check if the order has a customer associated with it
        if ($order->getCustomerId()) {
            $customer = $order->getCustomer();
            $shippingAddress = $order->getShippingAddress();
            
            $customerData = [
                'email' => $order->getCustomerEmail(),
                'phone' => $shippingAddress->getTelephone(),
                'first_name' => $shippingAddress->getFirstname(),
                'last_name' => $shippingAddress->getLastname(),
                'street_address' => $shippingAddress->getStreetLine(1),
                'city' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegionCode(),
                'postal_code' => $shippingAddress->getPostcode(),
                'country' => $shippingAddress->getCountryId(),
            ];
        }
        
        return $customerData;
    }
}
?>