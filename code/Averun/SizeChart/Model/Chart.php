<?php

/**
 * Chart.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model;

use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Registry;
use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Averun\SizeChart\Model\Chart\Attribute\Backend\ImageFactory;

class Chart extends AbstractModel implements IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'averun_sizechart_chart';

    /**
     * @var string
     */
    protected $_cacheTag = 'averun_sizechart_chart';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'averun_sizechart_chart';

    /**
     * @var ResourceModel\Dimension\Collection
     */
    protected $resourceDimensionCollection;

    /**
     * @var ResourceModel\Size\Collection
     */
    protected $resourceSizeCollection;

    protected $_chartId;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\ResourceModel\Chart');
    }

    /**
     * Chart constructor.
     * @param ImageFactory $imageFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ResourceModel\Size\Collection|null $resourceSizeCollection
     * @param ResourceModel\Dimension\Collection|null $resourceDimensionCollection
     * @param array $data
     */
    public function __construct(
        ImageFactory $imageFactory,
        Context $context,
        Registry $registry,
        ResourceModel\Size\Collection $resourceSizeCollection,
        ResourceModel\Dimension\Collection $resourceDimensionCollection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->imageFactory = $imageFactory;
        $this->resourceSizeCollection = $resourceSizeCollection;
        $this->resourceDimensionCollection = $resourceDimensionCollection;
    }

    /**
     * Save from collection data
     *
     * @param array $data
     * @return $this|bool
     */
    public function saveCollection(array $data)
    {
        if (isset($data[$this->getId()])) {
            $this->addData($data[$this->getId()]);
            $this->getResource()->save($this);
        }
        return $this;
    }

    /**
     * Get Image in right format to edit in admin form
     *
     * @return array
     */
    public function getImageValueForForm()
    {
        $image = $this->imageFactory->create();
        return $image->getFileValueForForm($this);
    }

    /**
     * Get Image Src to display in frontend
     *
     * @return mixed
     */
    public function getImageSrc()
    {
        $image = $this->imageFactory->create();
        return $image->getFileInfo($this)->getUrl();
    }

    public function beforeSave()
    {
        if (!$this->getId()) {
            $this->setUniqueIdentifier(EntityTypeInterface::CHART_CODE . '_entity');
        }
        return parent::beforeSave();
    }

    protected function _beforeLoad($id, $field = null)
    {
        $result = parent::_beforeLoad($id, $field);
        $this->_chartId = $id;
        return $result;
    }

    public function getSortSizes()
    {
        $items = $this->getSizes();
        $dimensions = $this->getDimensions();
        $sizes = [];
        $maxPosition = 0;
        if ($dimensions) {
            foreach ($dimensions as $dimension) {    //for sorting
                $sizes[$dimension['identifier']] = ['sizes' => []];
            }
        }
        if ($items) {
            foreach ($items as $item) {
                if (empty($sizes[$item['dimension_id']])) {
                    $sizes[$item['dimension_id']] = ['sizes' => []];
                }
                $sizes[$item['dimension_id']]['sizes'][$item['position']] = [
                    'name'     => $item['name'],
                    'id'       => $item['entity_id'],
                    'position' => $item['position']
                ];
                if ($maxPosition < (int)$item['position']) {
                    $maxPosition = (int)$item['position'];
                }
            }
        }
        return ['sizes' => $sizes, 'dimensions' => $dimensions, 'maxSizeAmount' => $maxPosition];
    }

    /**
     * @return array
     */
    public function getDimensions()
    {
        if (empty($this->_chartId)) {
            return null;
        }
        //$this->load($this->_chartId);
        $dimensions = [];
        if (($dimensionIds = $this->getData('dimension'))) {
            $dimensionIds = explode(',', $dimensionIds);
            $dimensionCollection = $this->resourceDimensionCollection
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('identifier', ['in' => $dimensionIds])
                ->setOrder('position', Collection::SORT_ORDER_ASC)
                ->load();
            foreach ($dimensionCollection as $dim) {
                $dimensions['dimension_' . $dim['identifier']] = [
                    'name'       => $dim['name'],
                    'id'         => $dim['identifier'],
//                    'id'         => $dim['entity_id'],
                    'identifier' => $dim['identifier'],
                    'main'       => $dim['main'],
                    'type'       => $dim['type'],
                    'lengthType' => $dim['length_type']
                ];
            }
        }
        return $dimensions;
    }

    public function getSizes()
    {
        if (empty($this->_chartId)) {
            return null;
        }
        //$this->load($this->_chartId);
        if (($dimensionIds = $this->getData('dimension'))) {
            $dimensionIds = explode(',', $dimensionIds);
        }
        /** @var $sizeCollection ResourceModel\Size\Collection */
        $sizeCollection = $this->resourceSizeCollection
            ->addFieldToFilter('chart_id', $this->getData('identifier'))
            ->addFieldToFilter('dimension_id', ['in' => $dimensionIds])
            ->addFieldToSelect('name')
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('dimension_id')
            ->addFieldToSelect('position')
            ->addOrder('position', Collection::SORT_ORDER_ASC)
            ->load();
        return $sizeCollection->getData();
    }
}
