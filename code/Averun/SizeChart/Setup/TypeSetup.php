<?php
/**
 * TypeSetup
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
class TypeSetup extends EavSetup
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
            'type'        => 'varchar',
            'label'       => 'Title',
            'input'       => 'text',
            'required'    => true, //true/false
            'sort_order'  => 10,
            'global'      => ScopedAttributeInterface::SCOPE_STORE,
            'group'       => 'General',
            'description' => 'Tops, Bottoms, Dresses, Swim, Suiting, Outerwear, Shoes, Accessories',
        ];
        $attributes['position'] = [
            'type'       => 'varchar',
            'label'      => 'Position',
            'input'      => 'text',
            'required'   => false, //true/false
            'sort_order' => 20,
            'global'     => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group'      => 'General',
        ];
        $attributes['is_active'] = [
            'type'       => 'int',
            'label'      => 'Is Active',
            'input'      => 'select',
            'source'     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'sort_order' => 10,
            'global'     => ScopedAttributeInterface::SCOPE_STORE,
            'group'      => 'General',
        ];

        return $attributes;
    }

    /**
     * Retrieve default entities: type
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            EntityTypeInterface::TYPE_CODE => [
                'entity_model'                => 'Averun\SizeChart\Model\ResourceModel\Type',
                'attribute_model'             => 'Averun\SizeChart\Model\ResourceModel\Eav\Attributes\Type',
                'table'                       => EntityTypeInterface::TYPE_CODE . '_entity',
                'increment_model'             => null,
                'additional_attribute_table'  => EntityTypeInterface::TYPE_CODE . '_eav_attribute',
                'entity_attribute_collection' => 'Averun\SizeChart\Model\ResourceModel\Attribute\Collections\Type',
                'attributes'                  => $this->getAttributes()
            ]
        ];

        return $entities;
    }
}
