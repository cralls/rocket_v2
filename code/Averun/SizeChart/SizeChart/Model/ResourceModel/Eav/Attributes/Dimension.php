<?php
/**
 * Dimension.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model\ResourceModel\Eav\Attributes;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Averun\SizeChart\Model\ResourceModel\Eav\Attribute;

class Dimension extends Attribute
{
    /**
     * Constants
     */
    const MODULE_NAME = 'Averun_Dimension';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = EntityTypeInterface::DIMENSION_CODE . '_entity_attribute';
}
