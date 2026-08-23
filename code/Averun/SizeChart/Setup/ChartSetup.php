<?php
/**
 * ChartSetup
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;

/**
 * @codeCoverageIgnore
 */
class ChartSetup extends EavSetup
{

    /**
     * Retrieve Entity Attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAttributes()
    {
        $attributes = [];
        $attributes['identifier'] = [
            'type'           => 'static',
            'label'          => 'identifier',
            'input'          => 'text',
            'required'       => true,
            'unique'         => true,
//            'backend'        => 'Magento\Catalog\Model\Product\Attribute\Backend\Sku',
            'sort_order'     => 10,
            'global'         => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'          => 'General',
            'validate_rules' => 'a:2:{s:15:"max_text_length";i:100;s:15:"min_text_length";i:1;}'
        ];
        $attributes['name'] = [
            'type'       => 'varchar',
            'label'      => 'Name',
            'input'      => 'text',
            'required'   => true, //true/false
            'sort_order' => 30,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];
        $attributes['category'] = [
            'type'       => 'varchar',
            'label'      => 'Category',
            'input'      => 'select',
            'required'   => true, //true/false
            'sort_order' => 50,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            'source'     => 'Averun\SizeChart\Model\Attribute\Source\Category',
        ];
        $attributes['type'] = [
            'type'       => 'varchar',
            'label'      => 'Type',
            'input'      => 'select',
            'required'   => true, //true/false
            'sort_order' => 60,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            'source'     => 'Averun\SizeChart\Model\Attribute\Source\Type',
        ];
        $attributes['dimension'] = [
            'type'       => 'varchar',
            'label'      => 'Dimension',
            'input'      => 'multiselect',
            'required'   => true, //true/false
            'sort_order' => 70,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            'backend'    => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'note'       => 'There are columns for size table. Includes dimensions and regions. Two in one. '
                            . 'Click Save to apply the changes about the columns',
            'source'     => 'Averun\SizeChart\Model\Attribute\Source\Dimension',
        ];
        $attributes['product_category'] = [
            'group'      => 'General',
            'type'       => 'varchar',
            'sort_order' => 80,
            'label'      => 'Product Category',
            'required'   => false, //true/false
            'input'      => 'multiselect',
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'backend'    => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'note'       => 'For products in selected categories will be available this table sizes. '
                            . 'For one category will always be only one table available sizes',
            'source'     => 'Averun\SizeChart\Model\Attribute\Source\ProductCategory',
        ];
        $attributes['description'] = [
            'type'            => 'text',
            'label'           => 'Description',
            'input'           => 'textarea',
            'required'        => false, //true/false
            'sort_order'      => 90,
            'global'          => ScopedAttributeInterface::SCOPE_STORE,
            'group'           => 'General',
            'wysiwyg_enabled' => true,
        ];
        $attributes['note'] = [
            'type'       => 'varchar',
            'label'      => 'Note',
            'input'      => 'text',
            'required'   => false, //true/false
            'sort_order' => 100,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];
        $attributes['image'] = [
            'type'       => 'varchar',
            'label'      => 'Image',
            'input'      => 'image',
            'backend'    => 'Averun\SizeChart\Model\Chart\Attribute\Backend\Image',
            'required'   => false, //true/false
            'sort_order' => 110,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];
        $attributes['is_active'] = [
            'type'       => 'int',
            'label'      => 'Is Active',
            'input'      => 'select',
            'source'     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'sort_order' => 120,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];
        return $attributes;
    }

    /**
     * Retrieve default entities: chart
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            EntityTypeInterface::CHART_CODE => [
                'entity_model'                => 'Averun\SizeChart\Model\ResourceModel\Chart',
                'attribute_model'             => 'Averun\SizeChart\Model\ResourceModel\Eav\Attributes\Chart',
                'table'                       => EntityTypeInterface::CHART_CODE . '_entity',
                'increment_model'             => null,
                'additional_attribute_table'  => EntityTypeInterface::CHART_CODE . '_eav_attribute',
                'entity_attribute_collection' => 'Averun\SizeChart\Model\ResourceModel\Attribute\Collections\Chart',
                'attributes'                  => $this->getAttributes()
            ]
        ];
        return $entities;
    }
}
