<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Position extends \Magento\Framework\DataObject
{
    const MAXIMUM_COMMISSION = 1;
    const MAXIMUM_DISCOUNT   = 2;
    const PROGRAM_PRIORITY   = 3;

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            self::MAXIMUM_COMMISSION => __('By maximum commission'),
            self::MAXIMUM_DISCOUNT => __('By maximum discount'),
            self::PROGRAM_PRIORITY => __('By program priority')
        ];
    }
}
