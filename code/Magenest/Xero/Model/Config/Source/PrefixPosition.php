<?php
namespace Magenest\Xero\Model\Config\Source;

/**
 * Class CronTime
 * @package Magenest\Xero\Model\Config\Source
 */
class PrefixPosition implements \Magento\Framework\Option\ArrayInterface
{
    const PREFIX = 1;
    const SUFFIX = 2;
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PREFIX,
                'label' => __('Prefix')
            ],
            [
                'value' => self::SUFFIX,
                'label' => __('Suffix')
            ],

        ];
    }
}
