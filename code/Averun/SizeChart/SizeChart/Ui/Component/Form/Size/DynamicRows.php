<?php
/**
 * DataProvider
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Ui\Component\Form\Size;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\DynamicRows as DynamicRowsCore;
use Magento\Ui\Component\Form\FieldFactory;
use Averun\SizeChart\Model\ResourceModel;
use Averun\SizeChart\Model\ChartFactory;

class DynamicRows extends DynamicRowsCore
{
    /**
     * @var FieldFactory
     */
    private $fieldFactory;
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /** @var chartFactory $chartFactory */
    protected $chartFactory;

    /**
     * @var ResourceModel\Dimension\Collection
     */
    protected $resourceDimensionCollection;

    private $dimensions;
    private $chartData;

    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        FieldFactory $fieldFactory,
        ChartFactory $chartFactory,
        Registry $registry,
        ResourceModel\Dimension\Collection $resourceDimensionCollection
    ) {
    
        parent::__construct($context, $components, $data);
        $this->_coreRegistry = $registry;
        $this->chartFactory = $chartFactory;
        $this->resourceDimensionCollection = $resourceDimensionCollection;
        $this->fieldFactory = $fieldFactory;
        $this->initDefaultValues();
        $this->addRowComponents();
    }

    protected function initDefaultValues()
    {
        $chartData = $this->getChartData();
        if (empty($chartData) || empty($chartData['dimension'])) {
            return $this;
        }
        $chartDimensions = $chartData['dimension'];
        $chartDimensions = explode(',', $chartDimensions);
        $this->dimensions = $this->resourceDimensionCollection
                                        ->addAttributeToSelect('name')
                                        ->addAttributeToFilter('identifier', ['in' => $chartDimensions])
                                        ->setOrder('position')
                                        ->load()
                                        ->toOptionArray('identifier');
        return $this;
    }

    public function addRowComponents()
    {
        if (empty($this->dimensions)) {
            return $this;
        }
        $fields = [];
        $i = 1;
        foreach ($this->dimensions as $dimension) {
            $fields[] = [
                'label'       => __($dimension['label']),
                'name'        => trim($dimension['value']),
                'value'       => 0,
                'formElement' => 'input',
                'sortOrder'   => 10 * $i++
            ];
        }
        foreach ($fields as $k => $fieldConfig) {
            $fieldInstance = $this->fieldFactory->create();
            $name = $fieldConfig['name'];
            $fieldInstance->setData(
                [
                    'config' => $fieldConfig,
                    'name'   => $name,
                ]
            );
            $fieldInstance->prepare();
            $this->getComponent('record')->addComponent($name, $fieldInstance);
        }
        $this->addDeleteButton($i);
        return $this;
    }

    protected function getChartData()
    {
        if (empty($this->chartData)) {
            $objectInstance = $this->chartFactory->create();
            $id = $this->_coreRegistry->registry('entity_id');
            $objectInstance->load($id);
            $this->chartData = $objectInstance->getData();
        }
        return $this->chartData;
    }

    /**
     * @param $i
     */
    protected function addDeleteButton($i)
    {
        $fieldInstance = $this->fieldFactory->create();
        $fieldInstance->setData(
            [
                'config' => [
                    'name'        => 'delete',
                    'formElement' => 'actionDelete',
                    'dataType'    => 'text',
                    'fit'         => false,
                    'sortOrder'   => 10 * $i
                ],
                'name'   => 'actionDelete',
            ]
        );
        $fieldInstance->prepare();
        $this->getComponent('record')->addComponent('actionDelete', $fieldInstance);
    }
}
