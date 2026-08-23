<?php
/**
 * DataProvider
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Ui\Component\Form\Chart;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Averun\SizeChart\Model\ResourceModel\Chart\Collection;
use Averun\SizeChart\Model\ResourceModel\Size\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;
    
    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CollectionFactory $sizeCollectionFactory
     */
    protected $sizeCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param FilterPool $filterPool
     * @param RequestInterface $request
     * @param CollectionFactory $sizeCollectionFactory
     * @param CategoryCollectionFactory $catalogResourceModelCategoryCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        FilterPool $filterPool,
        RequestInterface $request,
        CollectionFactory $sizeCollectionFactory,
        CategoryCollectionFactory $catalogResourceModelCategoryCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->sizeCollectionFactory = $sizeCollectionFactory;
        $this->collection = $collection;
        $this->filterPool = $filterPool;
        $this->request = $request;
        $this->catalogResourceModelCategoryCollectionFactory = clone $catalogResourceModelCategoryCollectionFactory;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->loadedData) {
            $storeId = (int)$this->request->getParam('store');
            $this->collection
                ->setStoreId($storeId)
                ->addAttributeToSelect('*');
            $items = $this->collection->getItems();
            foreach ($items as $chart) {
                $productCategories = implode(',', $this->getProductCategories($chart->getId()));
                $chart->setProductCategory($productCategories);
                $sizes = $this->getSizes($chart->getData('identifier'));
                if ($chart->getImage()) {
                    $chart->setImage($chart->getImageValueForForm());
                }
                $chart->setStoreId($storeId);
                $this->loadedData[$chart->getId()] = $chart->getData();
                $this->loadedData[$chart->getId()]['size'] = $sizes;
                break;
            }
        }
        return $this->loadedData;
    }

    /**
     * @param $chartId
     * @return array
     */
    protected function getProductCategories($chartId)
    {
        $categories = [];
        $existsCategories = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->addAttributeToFilter('ave_size_chart', $chartId)
            ->load();
        foreach ($existsCategories as $category) {
            $categories[] = $category->getId();
        }
        return $categories;
    }

    /**
     * @param $chartIdentifier
     * @return array
     */
    protected function getSizes($chartIdentifier)
    {
        $sizeCollection = $this->sizeCollectionFactory->create();
        $sizeCollection->addFieldToFilter('chart_id', $chartIdentifier);
        $sizeCollection->setOrder('position', Collection::SORT_ORDER_ASC);
        $sizeCollection->getItems();
        $sizes = [];
        foreach ($sizeCollection->getItems() as $item) {
            if (empty($sizes[$item->getData('position')])) {
                $sizes[$item->getData('position')] = [];
            }
            $sizes[$item->getData('position')][$item->getData('dimension_id')] = $item->getData('name');
        }
        return $sizes;
    }
}
