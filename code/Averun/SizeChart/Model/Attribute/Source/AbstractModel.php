<?php
namespace Averun\SizeChart\Model\Attribute\Source;

use Averun\SizeChart\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Db\Ddl\Table;

abstract class AbstractModel extends AbstractSource
{

    protected $tableComment;

    /**
     * @var AbstractCollection
     */
    protected $defaultModelCollection;

    /**
     * @var OptionFactory
     */
    protected $eavResourceModelEntityAttributeOptionFactory;

    /**
     * @var ResourceModel\Category\Collection
     */
    protected $resourceCategoryCollection;

    /**
     * @var ResourceModel\Chart\Collection
     */
    protected $resourceChartCollection;

    /**
     * @var ResourceModel\Type\Collection
     */
    protected $resourceTypeCollection;

    public function __construct(
        OptionFactory $eavResourceModelEntityAttributeOptionFactory,
        ResourceModel\Category\Collection $resourceCategoryCollection,
        ResourceModel\Chart\Collection $resourceChartCollection,
        ResourceModel\Type\Collection $resourceTypeCollection
    ) {
        $this->eavResourceModelEntityAttributeOptionFactory = $eavResourceModelEntityAttributeOptionFactory;
        $this->resourceCategoryCollection = $resourceCategoryCollection;
        $this->resourceChartCollection = $resourceChartCollection;
        $this->resourceTypeCollection = $resourceTypeCollection;
        $this->initDefaultModelCollection();
    }

    protected function initDefaultModelCollection()
    {
        $this->defaultModelCollection = $this->resourceCategoryCollection;
    }

    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->defaultModelCollection
                ->addAttributeToSelect('name')
                ->setOrder('position')
                ->load()
                ->toOptionArray('identifier');
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, ['value'=>'', 'label'=>__('Select...')]);
        }
        return $options;
    }

    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => true,
                'default'  => null,
                'extra'    => null,
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => $attributeCode . $this->tableComment,
            ],
        ];
    }

    public function getFlatUpdateSelect($store)
    {
        return $this->eavResourceModelEntityAttributeOptionFactory->create()
            ->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
