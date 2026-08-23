<?php

namespace MW\Affiliate\Model;

class Status extends \Magento\Framework\DataObject
{
    const PENDING   = 1;
    const COMPLETE  = 2;
    const CANCELED    = 3;
    const CLOSED    = 4;
    const INVOICED    = 5;
    const HOLDING     = 6;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::PENDING   => __('Pending'),
            self::CANCELED    => __('Cancelled'),
            self::COMPLETE  => __('Complete'),
            self::CLOSED      => __('Closed'),
            self::HOLDING    => __('Holding')
        ];
    }

    /**
     * @return array
     */
    public static function getOptionAction()
    {
        return [
            self::CANCELED  => __('Cancelled'),
            self::COMPLETE  => __('Complete'),
            self::CLOSED      => __('Closed'),
        ];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }
}
