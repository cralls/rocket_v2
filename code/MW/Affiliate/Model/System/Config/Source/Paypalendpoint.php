<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Paypalendpoint extends \Magento\Framework\DataObject
{
    /**
     * Options getter
     *
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Live')
            ],
            [
                'value' => 0,
                'label' => __('Sandbox')
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @param  array $key
     * @return array
     */
    public function toArray(array $keys = [])
    {
        return self::toOptionArray();
    }
}
