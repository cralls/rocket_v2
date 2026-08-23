<?php

namespace MW\Affiliate\Model;

class Statusactive extends \Magento\Framework\DataObject
{
    const PENDING       = 1;
    const ACTIVE        = 2;
    const INACTIVE        = 3;
    const NOTAPPROVED    = 4;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::PENDING       => __('Pending'),
            self::ACTIVE          => __('Active'),
            self::INACTIVE      => __('Inactive'),
            self::NOTAPPROVED     => __('Not Approved')
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
