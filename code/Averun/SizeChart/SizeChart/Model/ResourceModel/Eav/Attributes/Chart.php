<?php
/**
 * Chart.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model\ResourceModel\Eav\Attributes;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Averun\SizeChart\Model\ResourceModel\Eav\Attribute;

class Chart extends Attribute
{
    /**
     * Constants
     */
    const MODULE_NAME = 'Averun_Chart';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = EntityTypeInterface::CHART_CODE . '_entity_attribute';
}
