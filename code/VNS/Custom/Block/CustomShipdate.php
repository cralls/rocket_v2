<?php
namespace VNS\Custom\Block;

use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;

class CustomShipdate extends Template
{
    protected $orderFactory;
    protected $productFactory;
    protected $categoryFactory;
    
    public function __construct(
        Template\Context $context,
        OrderInterfaceFactory $orderFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        array $data = []
        ) {
            $this->orderFactory = $orderFactory;
            $this->productFactory = $productFactory;
            $this->categoryFactory = $categoryFactory;
            parent::__construct($context, $data);
    }
    
    public function getOrder()
    {
        if (!$this->hasData('order')) {
            $order = $this->orderFactory->create()->load(17803);
            //$order = $this->orderFactory->create()->load($this->getData('order_id'));
            $this->setData('order', $order);
        }
        return $this->getData('order');
    }
    
    public function getOrderCreatedat()
    {
        $order = $this->getOrder();
        $items = $order->getAllItems();
        foreach ($items as $item) {
            //error_log("ID is ".$item->getSku()."\r\n", 3, '/home/'.get_current_user().'/public_html/error_log');
            $product = $this->productFactory->create()->loadByAttribute('sku', $item->getSku());
            if (!$product) {
                continue; // Skip if the product doesn't exist
            }
            
            $categoryIds = $product->getCategoryIds();
            if (!is_array($categoryIds)) {
                continue;
            }
            
            //error_log("IDs are ".print_r($categoryIds, 1)."\r\n", 3, '/home/'.get_current_user().'/public_html/error_log');
            
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryFactory->create()->load($categoryId);
                if ($category->getParentId() == '108') {
                    $dates = explode(",", $category->getData('custom_attribute'));
                    if (!isset($dates[2])) {
                        continue;
                    }
                    $shipDate = date('m/d/Y', strtotime(trim($dates[1])));
                    return $shipDate;
                }
            }
        }
        
        $shipDate = date('m/d/Y', strtotime("+4 weeks", strtotime($this->getData('order_created_at'))));
        return $shipDate;
    }
}
