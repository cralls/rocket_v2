<?php
namespace VNS\Custom\Block;
class TeamPortal extends \Magento\Framework\View\Element\Template
{
        protected $registry;
        
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $resourceConfigurable,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->categoryFactory = $categoryFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->resourceProduct = $resourceProduct;
        $this->resourceConfigurable = $resourceConfigurable;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        $product = $this->registry->registry('current_product');
        $categoryIds = $product->getCategoryIds();
        foreach($categoryIds as $categoryId) {
            $category = $this->categoryFactory->create()->load($categoryId);
            if($category->getParentId() == '108') {
                if($category->getName() == 'LandShark') {
                    $this->setLandshark(1);
                    $sold = 0;
                    $orders = $this->orderCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter('team_portal', $category->getId());
                    foreach($orders as $order) {
                        $dates = explode(",", $category->getData('custom_attribute'));
                        if(!isset($dates[2])) continue;
                        $startDate = strtotime(trim($dates[2]));
                        $endDate = strtotime(trim($dates[0]));
                        $orderDate = strtotime($order->getCreatedAt());
                        if($orderDate > $startDate && $orderDate < $endDate) {
                            $items = $order->getAllItems();
                            foreach($items as $item) {
                                if($item->getSku() == $product->getSku()) $sold += $item->getQtyOrdered();
                                // Check parents
                                $parentIds = $this->resourceConfigurable->getParentIdsByChild($item->getProductId());
                                if (!empty($parentIds)) {
                                    $skus = $this->resourceProduct->getProductsSku($parentIds);
                                    if($skus[0]['sku'] == $product->getSku()) $sold += $item->getQtyOrdered();
                                }
                            }
                        }
                    }
                    $this->setSold($sold);
                }
            }
        }
        return parent::_prepareLayout();
    }
    
}
?>
