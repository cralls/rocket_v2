<?php

namespace MW\Affiliate\Model;

class Statusreferral extends \Magento\Framework\DataObject
{
    const ENABLED   = 1;
    const LOCKED    = 2;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::ENABLED   => __('Enable'),
            self::LOCKED    => __('Disable')
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
