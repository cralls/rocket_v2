<?php

namespace MW\Affiliate\Model;

class Orderstatus extends \Magento\Framework\DataObject
{
    const PENDING    = 1;
    const COMPLETE    = 2;
    const CANCELED    = 3;
    const CLOSED    = 4;
    const HOLDING    = 5;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::PENDING   => __('Pending'),
            self::COMPLETE    => __('Complete'),
            self::CANCELED  => __('Canceled'),
            self::CLOSED      => __('Closed'),
            self::HOLDING    => __('Holding')
        ];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getLabel($type)
    {
        $options = self::getOptionArray();

        return $options[$type];
    }
}
