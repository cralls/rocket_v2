<?php
/**
 * TruncateData
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Model\ResourceModel;

/**
 * @codeCoverageIgnore
 */
class TruncateData
{

    /** @var ResourceModel\Type\Collection */
    protected $resourceTypeCollection;
    /** @var ResourceModel\Dimension\Collection */
    protected $resourceDimensionCollection;
    /** @var ResourceModel\Category\Collection */
    protected $resourceCategoryCollection;
    /** @var ResourceModel\Chart\Collection */
    protected $resourceChartCollection;
    /** @var ResourceModel\Size */
    private $sizeResource;
    /** @var ResourceModel\Member */
    private $memberResource;
    /** @var ResourceModel\MemberMeasure */
    private $measureResource;

    /**
     * TruncateData constructor.
     * @param ResourceModel\Type\Collection $resourceTypeCollection
     * @param ResourceModel\Dimension\Collection $resourceDimensionCollection
     * @param ResourceModel\Chart\Collection $resourceChartCollection
     * @param ResourceModel\Category\Collection $resourceCategoryCollection
     * @param ResourceModel\Size $sizeResource
     * @param ResourceModel\MemberMeasure $measureResource
     * @param ResourceModel\Member $memberResource
     */
    public function __construct(
        ResourceModel\Type\Collection $resourceTypeCollection,
        ResourceModel\Dimension\Collection $resourceDimensionCollection,
        ResourceModel\Chart\Collection $resourceChartCollection,
        ResourceModel\Category\Collection $resourceCategoryCollection,
        ResourceModel\Size $sizeResource,
        ResourceModel\MemberMeasure $measureResource,
        ResourceModel\Member $memberResource
    ) {
        $this->resourceTypeCollection = $resourceTypeCollection;
        $this->resourceDimensionCollection = $resourceDimensionCollection;
        $this->resourceCategoryCollection = $resourceCategoryCollection;
        $this->resourceChartCollection = $resourceChartCollection;
        $this->sizeResource = $sizeResource;
        $this->memberResource = $memberResource;
        $this->measureResource = $measureResource;
    }


    public function truncate()
    {
        $this->resourceTypeCollection->delete();
        $this->resourceDimensionCollection->delete();
        $this->resourceCategoryCollection->delete();
        $this->resourceChartCollection->delete();
        $this->truncateTable($this->sizeResource->getConnection(), $this->sizeResource->getMainTable());
        $this->truncateTable($this->memberResource->getConnection(), $this->memberResource->getMainTable());
        $this->truncateTable($this->measureResource->getConnection(), $this->measureResource->getMainTable());
    }

    /**
     * @param $connection \Magento\Framework\DB\Adapter\AdapterInterface|false
     * @param $table string
     * @throws \Exception
     */
    private function truncateTable($connection, $table)
    {
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS = 0;');
            $connection->truncateTable($table);
            $connection->query('SET FOREIGN_KEY_CHECKS = 1;');
        } catch (\Exception $e) {
            $connection->query('SET FOREIGN_KEY_CHECKS = 1;');
            throw $e;
        }
    }
}
