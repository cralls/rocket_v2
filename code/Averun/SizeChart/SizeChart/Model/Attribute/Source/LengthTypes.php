<?php
/**
 * @copyright Copyright © 2020 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model\Attribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LengthTypes implements OptionSourceInterface
{
    const TYPE_LENGTH = 'is_length'; //only inch
    const TYPE_WEIGHT = 'is_weight'; //lbs
    const TYPE_HEIGHT = 'is_height'; //feet and inch

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('No need calculation'),
            ],
            [
                'value' => self::TYPE_WEIGHT,
                'label' => __('Weight'),
            ],
            [
                'value' => self::TYPE_HEIGHT,
                'label' => __('Height'),
            ],
            [
                'value' => self::TYPE_LENGTH,
                'label' => __('Length'),
            ],
        ];
    }
}
