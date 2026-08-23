<?php
namespace VNS\Custom\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;

     public function __construct(
        \Magento\Sales\Model\Order $order,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {
        $this->order = $order;
		$this->customerSession = $customerSession;
		$this->product = $product;
		$this->categoryFactory = $categoryFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       $orderIds = $observer->getEvent()->getOrderIds();
       if(is_array($orderIds)) {
           foreach ($orderIds as $orderId) {
                $order = $this->order->load($orderId);
                if($this->customerSession->getTeamPortal() > 0) {
				    $teamPortal = $this->customerSession->getTeamPortal();
                } else {
                    foreach($order->getAllItems() as $item) {
                        $product = $this->product->load($item->getProductId());
                        $categoryIds = $product->getCategoryIds();
                        foreach($categoryIds as $categoryId) {
                            $childCategory = $this->categoryFactory->create()->load($categoryId);
                            if($childCategory->getParentId() == '108') {
                                $category = $childCategory;
                                $teamPortal = $category->getId();
                            }
                        }
                    }
                }
                if(isset($teamPortal)) {
				    $order->setTeamPortal($teamPortal);
				    $order->save();
                }
           }
       } else {
           return true;
       }

       
    }
    
 
}