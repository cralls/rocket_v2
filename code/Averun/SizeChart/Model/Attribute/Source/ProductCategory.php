<?php
namespace Averun\SizeChart\Model\Attribute\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionsCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;

class ProductCategory extends Table
{

    /**
     * @var CollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    public function __construct(
        OptionsCollectionFactory $attrOptionCollectionFactory,
        OptionFactory $attrOptionFactory,
        CollectionFactory $catalogResourceModelCategoryCollectionFactory
    ) {
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_attrOptionFactory = $attrOptionFactory;
        $this->catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * get possible values
     *
     * @access public
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     * @author avemagento
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (is_null($this->_options)) {
            $this->_options = $this->getCategoriesArray();
        }
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, ['value'=>'', 'label'=>'']);
        }
        return $options;
    }

    /**
     * get options as array
     *
     * @access public
     * @param bool $withEmpty
     * @return string
     * @author avemagento
     */
    public function getOptionsArray($withEmpty = true)
    {
        $options = [];
        foreach ($this->getAllOptions($withEmpty) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    public function getCategoriesArray()
    {
        $categoriesArray = $this->catalogResourceModelCategoryCollectionFactory->create()->addAttributeToSelect('name')
            ->addAttributeToSort('path', 'asc')->load()->toArray();
        $categories = [];
        foreach ($categoriesArray as $categoryId => $category) {
            if (isset($category['name']) && isset($category['level'])) {
                $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
                $multiplier = ($category['level'] - 1) * 4;
                if ($multiplier > 0) {
                    $padding = str_repeat($nonEscapableNbspChar, $multiplier);
                } else {
                    $padding = '';
                }
                $categories[] = [
                    'label' => $padding . $category['name'],
                    'level' => $category['level'],
                    'value' => $categoryId
                ];
            }
        }
        return $categories;
    }
}
