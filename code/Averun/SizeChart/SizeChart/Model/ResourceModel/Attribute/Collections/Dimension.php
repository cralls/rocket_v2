<?php
/**
 * Dimension.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model\ResourceModel\Attribute\Collections;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Averun\SizeChart\Model\ResourceModel\Attribute\Collection;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;

class Dimension extends Collection
{
    /**
     * Entity factory
     *
     * @var EavEntityFactory
     */
    protected $_eavEntityFactory;

    /**
     * Main select object initialization.
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainTable()]
        )->where(
            'main_table.entity_type_id=?',
            $this->_eavEntityFactory->create()->setType(EntityTypeInterface::DIMENSION_CODE)->getTypeId()
        )->join(
            ['additional_table' => $this->getTable(EntityTypeInterface::DIMENSION_CODE. '_eav_attribute')],
            'additional_table.attribute_id = main_table.attribute_id'
        );
        return $this;
    }
}
