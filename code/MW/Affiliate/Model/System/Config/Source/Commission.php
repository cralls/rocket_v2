<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Commission extends \Magento\Framework\DataObject
{
    const BEFORE = 1;
    const AFTER  = 2;

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            self::BEFORE => __('Before Discount'),
            self::AFTER => __('After Discount')
        ];
    }
}
