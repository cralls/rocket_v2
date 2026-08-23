<?php

namespace Averun\SizeChart\Block\Frontend\Product;

use Averun\SizeChart\Model\Attribute\Source\UnitOfLength;
use Averun\SizeChart\Model\ChartFactory;
use Averun\SizeChart\Model\Member;
use Averun\SizeChart\Model\MemberMeasure;
use Averun\SizeChart\Model\ResourceModel\Chart\Collection;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class View extends Template
{
    protected $_template = 'catalog/product/sizechart/view.phtml';
    private $measurementData;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var UnitOfLength
     */
    private $unitOfLengthSource;
    /**
     * @var Member
     */
    private $modelMember;
    /**
     * @var MemberMeasure
     */
    private $modelMemberMeasure;
    /**
     * @var ChartFactory
     */
    protected $chartFactory;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var CategoryFactory
     */
    protected $catalogCategoryFactory;
    protected $chartId;
    protected $charts = [];

    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $registry,
        CategoryFactory $catalogCategoryFactory,
        UnitOfLength $unitOfLength,
        ChartFactory $chartFactory,
        Member $modelMember,
        MemberMeasure $modelMemberMeasure,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->customerSession = $customerSession;
        $this->registry = $registry;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->unitOfLengthSource = $unitOfLength;
        $this->modelMember = $modelMember;
        $this->modelMemberMeasure = $modelMemberMeasure;
        $this->chartFactory = $chartFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getImageAlias()
    {
        $chartId = $this->getCurrentChartId();
        if ($chartId) {
            $chart = $this->getChart();
            return $this->getMediaAlias() . $chart['identifier'] . '/';
        } else {
            return '';
        }
    }

    public function getMediaAlias($dir = 'image')
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
               . 'averun/sizechart/' . $dir . '/';
    }

    public function getButtonIcon()
    {
        return $this->_scopeConfig->getValue('ave_sizechart/general/icon', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getMembers()
    {
        if (!$this->isLoggedIn()) {
            return [];
        }
        return $this->modelMember->getCustomerMembers();
    }

    public function getMembersMeasurements($customerId = null)
    {
        if (!$this->isLoggedIn()) {
            return [];
        }
        if (empty($this->measurementData)) {
            if (empty($customerId)) {
                $customerId = $this->customerSession->getCustomer()->getId();
            }
            $measureCollection = $this->modelMemberMeasure->getCollection();
            $measureCollection->addFieldToFilter('customer_id', $customerId);
            $measureCollection->load();
            $data = $measureCollection->getData();
            $measurementsSorted = [];
            foreach ($data as $measure) {
                if (empty($measurementsSorted[$measure['member_id']])) {
                    $measurementsSorted[$measure['member_id']] = [];
                }
                $measurementsSorted[$measure['member_id']][$measure['dimension_id']] = $measure['value'];
            }
            $this->measurementData = $measurementsSorted;
        }
        return $this->measurementData;
    }

    public function getChart()
    {
        $chartId = $this->getCurrentChartId();
        return $this->getChartById($chartId);
    }

    /**
     * @param $chartId
     *
     * @return string
     */
    public function getChartById($chartId)
    {
        if (!empty($chartId)) {
            /** @var ChartFactory $chart */
            $chart = $this->chartFactory->create()->load($chartId);
            $chart->setStoreId($this->_storeManager->getStore()->getId());
            if (!$chart->getId()) {
                return '';
            }
            return $chart->getData() + $chart->getSortSizes();
        }
        return '';
    }

    public function getUnitOfLengthList()
    {
        return $this->unitOfLengthSource->getAllOptions(false);
    }

    public function getDefaultUnitOfLength()
    {
        $defaultUnit = $this->_scopeConfig->getValue('ave_sizechart/general/unit_of_length', ScopeInterface::SCOPE_STORE);
        if (empty($defaultUnit)) {
            $defaultUnit = UnitOfLength::DIMENSION_DEFAULT;
        }
        return $defaultUnit;
    }

    /**
     * @param $chartIdentifier
     *
     * @return int|string
     */
    protected function checkChartStatus($chartIdentifier)
    {
        if (empty($chartIdentifier)) {
            return 0;
        }

        if (empty($this->charts)) {
            /** @var Collection $chartCollection */
            $chartCollection = $this->chartFactory->create()
                ->getCollection()
                ->addFieldToSelect('is_active');
            foreach ($chartCollection as $chartItem) {
                $this->charts[$chartItem->getId()] = $chartItem->getData();
            }
        }

        if (!empty($this->charts[$chartIdentifier]) && !empty($this->charts[$chartIdentifier]['is_active'])) {
            return $chartIdentifier;
        } else {
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return int|null
     */
    protected function getCurrentChartId()
    {
        if (empty($this->chartId)) {
            $chartId = $this->getChartId(); //try get id from static block if added via csm page
            if (empty($chartId)) {
                $current_product = $this->registry->registry('current_product');
                $chartId = $this->checkChartStatus($current_product->getData('ave_size_chart'));
            }

            if (empty($chartId)) {
                $productCategories = $current_product->getCategoryIds();
                if (!empty($productCategories)) {
                    $chartId = $this->getChartIdFromCategories($productCategories);
                }
            }

            $this->chartId = $chartId;
        }

        return $this->chartId;
    }

    /**
     * @param $productCategories
     * @return int|mixed
     */
    protected function getChartIdFromCategories($productCategories)
    {
        $chartId = 0;
        $productCategoriesInt = [];
        foreach ($productCategories as $categoryId) {
            $productCategoriesInt[] = (int) $categoryId;
        }

        rsort($productCategoriesInt);
        $parentIds = [];
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($productCategoriesInt as $categoryId) {
            if (empty($chartId)) {
                if (empty($categoryId)) {
                    continue;
                }
                $category = $this->categoryRepository->get($categoryId);
                $chartId = $category->getData('ave_size_chart');
                $chartId = $this->checkChartStatus($chartId);
                $parentIds[] = $category->getParentId();
            }
        }

        while (empty($chartId) && !empty($parentIds)) {
            $parentIds2 = [];
            foreach ($parentIds as $categoryId) {
                if (empty($chartId)) {
                    if (empty($categoryId)) {
                        continue;
                    }
                    $category = $this->categoryRepository->get($categoryId);
                    $chartId = $category->getData('ave_size_chart');
                    $chartId = $this->checkChartStatus($chartId);
                    $parentIds2[] = $category->getParentId();
                } else {
                    break;
                }
            }
            $parentIds = $parentIds2;
        }

        return $chartId;
    }

    /**
     * @return \Magento\Framework\View\Asset\Repository
     */
    public function getAssetRepository()
    {
        return $this->_assetRepo;
    }
}
