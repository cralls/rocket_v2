<?php

/**
 * Uninstall.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var array
     */
    protected $tablesToUninstall = [
        EntityTypeInterface::TYPE_CODE . '_entity',
        EntityTypeInterface::TYPE_CODE . '_eav_attribute',
        EntityTypeInterface::TYPE_CODE . '_entity_datetime',
        EntityTypeInterface::TYPE_CODE . '_entity_decimal',
        EntityTypeInterface::TYPE_CODE . '_entity_int',
        EntityTypeInterface::TYPE_CODE . '_entity_text',
        EntityTypeInterface::TYPE_CODE . '_entity_varchar',
        EntityTypeInterface::CATEGORY_CODE . '_entity',
        EntityTypeInterface::CATEGORY_CODE . '_eav_attribute',
        EntityTypeInterface::CATEGORY_CODE . '_entity_datetime',
        EntityTypeInterface::CATEGORY_CODE . '_entity_decimal',
        EntityTypeInterface::CATEGORY_CODE . '_entity_int',
        EntityTypeInterface::CATEGORY_CODE . '_entity_text',
        EntityTypeInterface::CATEGORY_CODE . '_entity_varchar',
        EntityTypeInterface::DIMENSION_CODE . '_entity',
        EntityTypeInterface::DIMENSION_CODE . '_eav_attribute',
        EntityTypeInterface::DIMENSION_CODE . '_entity_datetime',
        EntityTypeInterface::DIMENSION_CODE . '_entity_decimal',
        EntityTypeInterface::DIMENSION_CODE . '_entity_int',
        EntityTypeInterface::DIMENSION_CODE . '_entity_text',
        EntityTypeInterface::DIMENSION_CODE . '_entity_varchar',
        EntityTypeInterface::CHART_CODE . '_entity',
        EntityTypeInterface::CHART_CODE . '_eav_attribute',
        EntityTypeInterface::CHART_CODE . '_entity_datetime',
        EntityTypeInterface::CHART_CODE . '_entity_decimal',
        EntityTypeInterface::CHART_CODE . '_entity_int',
        EntityTypeInterface::CHART_CODE . '_entity_text',
        EntityTypeInterface::CHART_CODE . '_entity_varchar',
        EntityTypeInterface::SIZE_CODE,
        EntityTypeInterface::MEMBER_CODE,
        EntityTypeInterface::MEMBER_MEASURE_CODE
    ];

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
    {
        $setup->startSetup();

        foreach ($this->tablesToUninstall as $table) {
            if ($setup->tableExists($table)) {
                $setup->getConnection()->dropTable($setup->getTable($table));
            }
        }

        $setup->endSetup();
    }
}
