<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Rocket\Catalog\Block\Category;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;

/**
 * Class View
 * @api
 * @package Magento\Catalog\Block\Category
 * @since 100.0.2
 */
class View extends \Magento\Catalog\Block\Category\View
{
    protected $categoryAttributeRepository;
    protected $categoryResourceModel;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        CategoryResourceModel $categoryResourceModel,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\Product $product,
        array $data = []
    )
    {
        $this->customerSession = $customerSession;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->categoryRepository = $categoryRepository;
        $this->product = $product;
        $this->registry = $registry;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }
    
    public function getCategoryParentId($order) {
        $category = $this->registry->registry('current_category');
        if(isset($category)) {
            return $category->getParentId();
        } else {
            // If an order is set lets check the items to see if any are in a Team Portal
            if(!is_null($order)) {
                $items = $order->getAllItems();
            
                if(is_array($items) || is_object($items)) {
                    foreach($items as $item) {
                        $product = $this->product->load($item->getProductId());
                        $categoryIds = $product->getCategoryIds();
                        foreach($categoryIds as $categoryId){
                            $cat = $this->categoryRepository->get($categoryId);
                            if($cat->getParentId() == '108') break;
                        }
                        return isset($cat) ? $cat->getParentId() : 0;
                    }
                }
            } else {
                return 0;
            }
        }
    }
    
    public function getShoppingEndDates($order = null)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        if(!is_null($order)) {
            //error_log("Order id for team portal email is ".$order->getId()."\r\n", 3, '/home/rocketsc/public_html/error_log_portal');
            if($order->getTeamPortal() > 0) {
                $category = $this->categoryRepository->get($order->getTeamPortal());
            } else {
                //error_log("Team portal is not set on order yet so going to set it"."\r\n", 3, '/home/rocketsc/public_html/error_log_portal');
                foreach($order->getAllItems() as $item) {
                    $product = $this->product->load($item->getProductId());
                    $categoryIds = $product->getCategoryIds();
                    foreach($categoryIds as $categoryId) {
                        $childCategory = $this->categoryRepository->get($categoryId);
                        if($childCategory->getParentId() == '108') {
                            //error_log("Child category is ".$categoryId." and parent is Team Portals"."\r\n", 3, '/home/rocketsc/public_html/error_log_portal');
                            $category = $childCategory;
                        }
                    }
                }
            }
        } elseif($this->customerSession->getTeamPortal() > 0) {
            $category = $this->categoryRepository->get($this->customerSession->getTeamPortal());
        } else {
            $category = $this->getCurrentCategory();
        }
        if(is_null($category)) return false;
        //$customAttribute = $category->getData('custom_attribute');
        //error_log("Category ID is ".$category->getId());
        $customAttribute = $this->categoryAttributeRepository->get('custom_attribute');
        $attributeRawValue = $this->categoryResourceModel->getAttributeRawValue(
            $category->getId(),
            $customAttribute->getAttributeId(),
            $category->getStoreId()
        );
        if (empty($attributeRawValue)) {
            return [];
        }
        $dates = explode(',', $attributeRawValue);
        $storeCloses = trim($dates[0]);
        $estShipping = trim($dates[1]);
        return ['storeCloses' => $storeCloses, 'estShipping' => $estShipping];
    }
}