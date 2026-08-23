<?php

namespace MW\Affiliate\Model;

class Statuswebsite extends \Magento\Framework\DataObject
{
    const UNVERIFIED = 0;
    const VERIFIED   = 1;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::UNVERIFIED => __('Not Verified'),
            self::VERIFIED      => __('Verified')
        ];
    }

    /**
     * @param $status
     * @return mixed
     */
    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }
}
