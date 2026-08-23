<?php

namespace MW\Affiliate\Model;

class Statusprogram extends \Magento\Framework\DataObject
{
    const ENABLED   = 1;
    const DISABLED  = 2;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::ENABLED   => __('Enabled'),
            self::DISABLED  => __('Disabled')
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
