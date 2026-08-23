<?php
namespace Averun\SizeChart\Model\Attribute\Source;

class UnitOfLength
{
    const DIMENSION_CM      = 'cm';
    const DIMENSION_INCH    = 'inch';
    const DIMENSION_DEFAULT = self::DIMENSION_CM;

    public function toOptionArray($withEmpty = false)
    {
        $options = [];
        if ($withEmpty) {
            $options[] = [
                'value' => '',
                'label' => __('Select a default dimension')
            ];
        }
        $options[] = ['value' => self::DIMENSION_CM, 'label' => self::DIMENSION_CM];
        $options[] = ['value' => self::DIMENSION_INCH, 'label' => self::DIMENSION_INCH];
        return $options;
    }

    public function getAllOptions($withEmpty = true)
    {
        $options = [];
        foreach ($this->toOptionArray($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function toArray($withEmpty = true)
    {
        return $this->getAllOptions($withEmpty);
    }
}
