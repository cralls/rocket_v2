<?php

namespace VNS\Admin\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class SalesOrderGridCollection
{
    /**
     * Modify the filter for the team_portal_name column.
     *
     * @param Collection $subject
     * @param string $field
     * @param string|array $condition
     * @return array
     */
    public function beforeAddFieldToFilter($subject, $field, $condition = null)
    {
        if ($field === 'team_portal_name') {
            // Redirect the filter to the actual database column
            $field = 'ccev.value';
        }
        
        return [$field, $condition];
    }
    
    /**
     * Add the category name (team_portal) column to the sales order grid collection.
     *
     * @param Collection $subject
     * @param \Magento\Framework\DB\Select|null $result
     * @return \Magento\Framework\DB\Select|null
     */
    public function afterGetSelect(Collection $subject, $result)
    {
        if ($result instanceof \Magento\Framework\DB\Select) {
            // Check if the joins already exist to avoid duplicate joins
            $fromPart = $result->getPart(\Magento\Framework\DB\Select::FROM);
            
            if (!isset($fromPart['ccev'])) {
                $result->joinLeft(
                    ['so' => $subject->getTable('sales_order')],
                    'main_table.entity_id = so.entity_id',
                    []
                    );
                
                $result->joinLeft(
                    ['ccev' => $subject->getTable('catalog_category_entity_varchar')],
                    'so.team_portal = ccev.entity_id AND ccev.attribute_id = 33',
                    ['team_portal_name' => 'ccev.value']
                    );
            }
        }
        
        return $result;
    }
}
