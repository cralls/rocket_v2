<?php

namespace Rss\CustomDesigns\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class YesNo implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __('Yes'), 'value' => 1],
            ['label' => __('No'), 'value' => 0],
        ];
    }
}