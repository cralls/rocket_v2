<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Period extends \Magento\Framework\DataObject
{
    const WEEKLY    = 1;
    const MONTHLY   = 2;

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            self::WEEKLY => __('Weekly'),
            self::MONTHLY => __('Monthly')
        ];
    }
}
