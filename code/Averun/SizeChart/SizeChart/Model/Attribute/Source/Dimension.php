<?php
namespace Averun\SizeChart\Model\Attribute\Source;

use Averun\SizeChart\Model\ResourceModel;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

class Dimension extends Table
{
    /**
     * @var ResourceModel\Dimension\Collection
     */
    protected $resourceDimensionCollection;

    public function __construct(
        CollectionFactory $attrOptionCollectionFactory,
        OptionFactory $attrOptionFactory,
        ResourceModel\Dimension\Collection $resourceDimensionCollection
    ) {
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_attrOptionFactory = $attrOptionFactory;
        $this->resourceDimensionCollection = clone $resourceDimensionCollection;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->resourceDimensionCollection
                                ->addAttributeToSelect('identifier')
                                ->addAttributeToSelect('name')
                                ->setOrder('position')
                                ->load()
                                ->toOptionArray('identifier');
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, ['value'=>'', 'label'=>'']);
        }
        return $options;
    }

    /**
     * @param bool $withEmpty
     * @return string
     */
    public function getOptionsArray($withEmpty = true)
    {
        $options = [];
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
