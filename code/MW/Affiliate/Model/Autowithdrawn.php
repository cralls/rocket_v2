<?php

namespace MW\Affiliate\Model;

class Autowithdrawn extends \Magento\Framework\DataObject
{
    const AUTO      = 1;
    const MANUAL    = 2;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::AUTO      => __('Auto'),
            self::MANUAL    => __('Manual')
        ];
    }

    /**
     * @param $gateway
     * @return mixed
     */
    public static function getLabel($gateway)
    {
        $options = self::getOptionArray();
        if (isset($options[$gateway])) {
            return $options[$gateway];
        }
        return '';
    }
}
