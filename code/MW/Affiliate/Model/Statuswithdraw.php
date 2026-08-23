<?php

namespace MW\Affiliate\Model;

class Statuswithdraw extends \Magento\Framework\DataObject
{
    const COMPLETE    = 2;
    const CANCELED    = 3;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::COMPLETE  => __('Complete'),
            self::CANCELED    => __('Canceled'),
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
