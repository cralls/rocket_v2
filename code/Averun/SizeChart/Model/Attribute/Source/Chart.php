<?php

namespace Averun\SizeChart\Model\Attribute\Source;

class Chart extends AbstractModel
{
    protected $tableComment = ' Chart column';

    protected function initDefaultModelCollection()
    {
        $this->defaultModelCollection = $this->resourceChartCollection;
    }

    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->defaultModelCollection
                ->addAttributeToSelect('name')
                ->setOrder('position')
                ->load()
                ->toOptionArray(null);
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, ['value'=>'', 'label'=>__('Select...')]);
        }
        return $options;
    }
}
