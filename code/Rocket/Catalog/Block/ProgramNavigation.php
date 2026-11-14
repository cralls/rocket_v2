<?php
/**
 * RSS 2026-11-14: build program navigation from the store root on every page.
 */
namespace Rocket\Catalog\Block;

use Magento\Catalog\Block\Navigation;

class ProgramNavigation extends Navigation
{
    public function getRootChildren()
    {
        $store = $this->_storeManager->getStore();

        return $this->_categoryInstance
            ->setStoreId($store->getId())
            ->load($store->getRootCategoryId())
            ->getChildrenCategories();
    }
}
