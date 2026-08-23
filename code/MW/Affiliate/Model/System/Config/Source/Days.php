<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Days extends \Magento\Framework\DataObject
{
    /**
     * @return array
     */
    public static function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Sunday')
            ],
            [
                'value' => 1,
                'label' => __('Monday')
            ],
            [
                'value' => 2,
                'label' => __('Tuesday')
            ],
            [
                'value' => 3,
                'label' => __('Wednesday')
            ],
            [
                'value' => 4,
                'label' => __('Thursday')
            ],
            [
                'value' => 5,
                'label' => __('Friday')
            ],
            [
                'value' => 6,
                'label' => __('Saturday')
            ]
        ];
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getLabel($status)
    {
        $options = self::toOptionArray();

        return $options[$status]['label'];
    }
}
