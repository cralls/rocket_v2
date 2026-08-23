<?php
/**
 * DimensionSetup
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
class DimensionSetup extends EavSetup
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
            'sort_order' => 10,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
            //'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
        ];
        $attributes['description'] = [
            'type'       => 'varchar',
            'label'      => 'Description',
            'input'      => 'text',
            'required'   => true, //true/false
            'sort_order' => 20,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
            //'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
        ];
        $attributes['type'] = [
            'type'       => 'varchar',
            'label'      => 'Type',
            'input'      => 'text',
            'required'   => true, //true/false
            'sort_order' => 30,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            //'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
        ];
        $attributes['main'] = [
            'type'       => 'int',
            'label'      => 'Main',
            'input'      => 'select',
            'source'     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'required'   => true, //true/false
            'sort_order' => 40,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            'default'    => 0
        ];
        $attributes['position'] = [
            'type'       => 'varchar',
            'label'      => 'Position',
            'input'      => 'text',
            'required'   => false, //true/false
            'sort_order' => 90,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
            //'validate_rules' => 'a:2:{s:15:"max_text_length";i:255;s:15:"min_text_length";i:1;}',
        ];
        $attributes['is_active'] = [
            'type'       => 'int',
            'label'      => 'Is Active',
            'input'      => 'select',
            'source'     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'sort_order' => 100,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];
        return $attributes;
    }

    /**
     * Retrieve default entities: dimension
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            EntityTypeInterface::DIMENSION_CODE => [
                'entity_model'                => 'Averun\SizeChart\Model\ResourceModel\Dimension',
                'attribute_model'             => 'Averun\SizeChart\Model\ResourceModel\Eav\Attributes\Dimension',
                'table'                       => EntityTypeInterface::DIMENSION_CODE . '_entity',
                'increment_model'             => null,
                'additional_attribute_table'  => EntityTypeInterface::DIMENSION_CODE . '_eav_attribute',
                'entity_attribute_collection' => 'Averun\SizeChart\Model\ResourceModel\Attribute\Collections\Dimension',
                'attributes'                  => $this->getAttributes()
            ]
        ];
        return $entities;
    }
}
