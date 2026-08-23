<?php
namespace VNS\Admin\Ui\Component\DataProvider\SearchResult;

class TeamOrders extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->getSelect()->joinLeft(
            ['catalog_category_entity_varchar' => $this->getTable('catalog_category_entity_varchar')],
            'main_table.team_portal = catalog_category_entity_varchar.entity_id',
            ['value', 'category_entity_id' => 'entity_id'] // Aliasing 'entity_id' from 'catalog_category_entity_varchar'
            );
        
        $this->addFieldToFilter('catalog_category_entity_varchar.attribute_id', ['eq' => 33]);
        $this->addFieldToFilter('main_table.team_portal', ['neq' => null]);
        
        return $this;
    }
    
}