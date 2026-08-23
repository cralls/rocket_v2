<?php
/**
 * Save
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Controller\Adminhtml\Chart;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Averun\SizeChart\Model\ResourceModel\Size\CollectionFactory;
use Averun\SizeChart\Model\SizeFactory;
use Averun\SizeChart\Model\ChartFactory;

class Save extends Action
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    /**
     * @var CategoryFactory
     */
    protected $catalogCategoryFactory;

    /** @var ChartFactory $chartFactory */
    protected $chartFactory;

    /**
     * @var CollectionFactory $sizeCollectionFactory
     */
    protected $sizeCollectionFactory;

    /**
     * @var SizeFactory
     */
    protected $sizeFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param CollectionFactory $sizeCollectionFactory
     * @param ChartFactory $chartFactory
     * @param SizeFactory $sizeFactory
     * @param CategoryFactory $catalogCategoryFactory
     * @param CategoryCollectionFactory $catalogResourceModelCategoryCollectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $sizeCollectionFactory,
        ChartFactory $chartFactory,
        SizeFactory $sizeFactory,
        CategoryFactory $catalogCategoryFactory,
        CategoryCollectionFactory $catalogResourceModelCategoryCollectionFactory
    ) {
        $this->chartFactory = $chartFactory;
        $this->sizeFactory = $sizeFactory;
        $this->sizeCollectionFactory = $sizeCollectionFactory;
        $this->catalogResourceModelCategoryCollectionFactory = clone $catalogResourceModelCategoryCollectionFactory;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Averun_SizeChart::chart');
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $data = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $params = [];
            $chart = $this->chartFactory->create();
            $chart->setStoreId($storeId);
            $params['store'] = $storeId;
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            } else {
                $chart->load($data['entity_id']);
                $params['entity_id'] = $data['entity_id'];
            }
            $this->imagePreprocessing($data);
            $chart->addData($data);

            $this->_eventManager->dispatch(
                'averun_sizechart_chart_prepare_save',
                ['object' => $this->chartFactory, 'request' => $this->getRequest()]
            );

            try {
                $chart->save();
                $chartId = $chart->getData('identifier');
                $this->saveDependencySizes($chartId);
                $productCategories = !empty($data['product_category']) ? $data['product_category'] : null;
                $this->saveDependencyCategory($chart->getId(), $productCategories);
                $this->messageManager->addSuccessMessage(__('You saved this record.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $params['entity_id'] = $chart->getId();
                    $params['_current'] = true;
                    return $resultRedirect->setPath('*/*/edit', $params);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the record.'));
            }

            $this->_getSession()->setFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit', $params);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Image data preprocessing
     *
     * @param array $data
     */
    protected function imagePreprocessing(&$data)
    {
        if (empty($data['image'])) {
            unset($data['image']);
            $data['image']['delete'] = true;
        }
    }

    protected function saveDependencyCategory($chartId, $categoryIds = [])
    {
        $existsCategories = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->addAttributeToFilter('ave_size_chart', $chartId)
            ->load();
        foreach ($existsCategories as &$category) {
            if (empty($categoryIds) || !in_array($category->getId(), $categoryIds)) {
                $category->setData('ave_size_chart', '0');
                $category->save();
            }
        }
        if (empty($categoryIds)) {
            return $this;
        }
        foreach ($categoryIds as $categoryId) {
            //todo: exclude existed categories
            $this->catalogCategoryFactory->create()->load($categoryId)->setData('ave_size_chart', $chartId)->save();
        }
        return $this;
    }

    protected function saveDependencySizes($chartIdentifier)
    {
        $sizeCollection = $this->sizeCollectionFactory->create();
        $sizeCollection->addFieldToFilter('chart_id', $chartIdentifier);
        $items = $sizeCollection->getItems();
        foreach ($items as $item) {
            $item->delete();
        }
        $sizeCollection->removeAllItems();
        $sizes = $this->getRequest()->getPost('size', []);
        usort(
            $sizes,
            function ($a, $b) {
                return $a["position"] - $b["position"];
            }
        );
        $position = 0;
        foreach ($sizes as $positions) {
            if (array_key_exists('delete', $positions) && $positions['delete']) {
                continue;
            }
            foreach ($positions as $dimensionKey => $name) {
                if (is_null($name) || in_array($dimensionKey, ['record_id', 'position', 'actionDelete', 'initialize'])) {
                    continue;
                }
                $size = $this->sizeFactory->create();
                $size->setData('chart_id', $chartIdentifier);
                $size->setData('dimension_id', $dimensionKey);
                $size->setData('position', $position);
                $size->setData('name', $name);
                $size->setData('status', 1);
                $size->save();
            }
            $position++;
        }
    }
}
