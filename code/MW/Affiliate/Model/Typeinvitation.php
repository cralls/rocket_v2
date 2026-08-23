<?php

namespace MW\Affiliate\Model;

class Typeinvitation extends \Magento\Framework\DataObject
{
    const NON_REFERRAL  = 0;
    const REFERRAL_LINK    = 1;
    const REFERRAL_CODE    = 2;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::NON_REFERRAL  => __('Non Referral'),
            self::REFERRAL_LINK => __('Referral Link'),
            self::REFERRAL_CODE => __('Referral Code')
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
