<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Signupform extends \Magento\Framework\DataObject
{
    const ENABLE_SIGNUP_CHECKOUT = 1;
    const ENABLE_SIGNUP_FORM     = 2;
    const DISABLE                = 3;

    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            self::ENABLE_SIGNUP_CHECKOUT => __('Enable, signup checkbox'),
            self::ENABLE_SIGNUP_FORM => __('Enable, signup form'),
            self::DISABLE => __('Disable')
        ];
    }
}
