<?php
/**
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model\Attribute\Source;

use Averun\SizeChart\Model\Entity\DimensionTypeInterface;
use Magento\Framework\Data\OptionSourceInterface;

class DimensionTypes implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Select...'),
            ],
            [
                'value' => DimensionTypeInterface::TYPE_REGION,
                'label' => __('Region'),
            ],
            [
                'value' => DimensionTypeInterface::TYPE_DIMENSION,
                'label' => __('Dimension'),
            ],
        ];
    }
}
