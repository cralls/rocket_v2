<?php

namespace MW\Affiliate\Model\System\Config\Source;

class Months extends \Magento\Framework\DataObject
{
    /**
     * @return array
     */
    public static function toOptionArray()
    {
        $output = [];
        for ($i = 1; $i <= 31; $i++) {
            $output[] = [
                'value' => $i,
                'label' => __('%1', $i)
            ];
        }

        return $output;
    }
}
