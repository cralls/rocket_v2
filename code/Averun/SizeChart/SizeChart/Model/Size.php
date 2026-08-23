<?php
namespace Averun\SizeChart\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Size extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'averun_sizechart_size';

    /**
     * @var DateTime
     */
    protected $dateTimeDateTime;

    /**
     * @var ModelFactory
     */
    protected $collectionFactory;

    /**
     * @var ResourceModel\Chart\Collection
     */
    protected $resourceChartCollection;

    /**
     * @var ResourceModel\Size\Collection
     */
    protected $resourceSizeCollection;


    protected $_eventPrefix = 'averun_sizechart_size';
    protected $_eventObject = 'size';

    /**
     * @var Chart
     */
    protected $modelChart;

    /**
     * @var Dimension
     */
    protected $modelDimension;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Size constructor.
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTimeDateTime
     * @param ModelFactory $collectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param ResourceModel\Chart\Collection|null $resourceChartCollection
     * @param ResourceModel\Size\Collection|null $resourceSizeCollection
     * @param Chart|null $modelChart
     * @param Dimension|null $modelDimension
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        DateTime $dateTimeDateTime,
        ModelFactory $collectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ResourceModel\Chart\Collection $resourceChartCollection = null,
        ResourceModel\Size\Collection $resourceSizeCollection = null,
        Chart $modelChart = null,
        Dimension $modelDimension = null,
        array $data = []
    ) {
    
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->dateTimeDateTime = $dateTimeDateTime;
        $this->resourceChartCollection = $resourceChartCollection;
        $this->resourceSizeCollection = $resourceSizeCollection;
        $this->modelChart = $modelChart;
        $this->modelDimension = $modelDimension;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\ResourceModel\Size');
    }

    public function getParentChart()
    {
        if (!$this->hasData('_parent_chart')) {
            if (!$this->getChartId()) {
                return null;
            } else {
                $chart = $this->modelChart->setStoreId($this->storeManager->getStore()->getId())
                    ->load($this->getChartId());
                if ($chart->getId()) {
                    $this->setData('_parent_chart', $chart);
                } else {
                    $this->setData('_parent_chart', null);
                }
            }
        }
        return $this->getData('_parent_chart');
    }

    public function getParentDimension()
    {
        if (!$this->hasData('_parent_dimension')) {
            if (!$this->getDimensionId()) {
                return null;
            } else {
                $dimension = $this->modelDimension->setStoreId($this->storeManager->getStore()->getId())
                    ->load($this->getDimensionId());
                if ($dimension->getId()) {
                    $this->setData('_parent_dimension', $dimension);
                } else {
                    $this->setData('_parent_dimension', null);
                }
            }
        }
        return $this->getData('_parent_dimension');
    }

    public function beforeSave()
    {
        parent::beforeSave();
        $now = $this->dateTimeDateTime->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    public function getAttributeText($attributeCode)
    {
        $text = $this->getResource()->getAttribute($attributeCode)->getSource()->getOptionText(
            $this->getData($attributeCode)
        );
        if (is_array($text)) {
            return implode(', ', $text);
        }
        return $text;
    }

    private function getChartId()
    {
        return $this->getData('chart_id');
    }

    private function getDimensionId()
    {
        return $this->getData('dimension_id');
    }
}
