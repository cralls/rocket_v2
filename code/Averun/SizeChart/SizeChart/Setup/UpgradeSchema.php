<?php
/**
 * upgradeSchema.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavTablesSetupFactory
     */
    protected $eavTablesSetupFactory;

    /**
     * UpgradeSchema constructor.
     * @param EavTablesSetupFactory $eavTablesSetupFactory
     */
    public function __construct(EavTablesSetupFactory $eavTablesSetupFactory)
    {
        $this->eavTablesSetupFactory = $eavTablesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->createChartSchema($setup);
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->createSizeSchema($setup);
        }
        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $this->createMemberSchema($setup);
            $this->createMemberMeasureSchema($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createMemberMeasureSchema(SchemaSetupInterface $setup)
    {
        $tableName = EntityTypeInterface::MEMBER_MEASURE_CODE;
        $tableMember = EntityTypeInterface::MEMBER_CODE;

        $table = $setup->getConnection()->newTable($setup->getTable($tableName))->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Measure ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
            ],
            'Customer ID'
        )->addColumn(
            'member_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
            ],
            'Member ID'
        )->addColumn(
            'dimension_id',
            Table::TYPE_TEXT,
            100,
            [
                'nullable' => false,
            ],
            'Dimension ID'
        )->addColumn(
            'value',
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
            ],
            'Dimension value'
        )->addIndex(
            $setup->getIdxName($tableName, ['customer_id']),
            ['customer_id']
        )->addIndex($setup->getIdxName($tableName, ['member_id']), ['member_id'])->addIndex($setup->getIdxName($tableName, ['dimension_id']), ['dimension_id'])->addForeignKey(
            $setup->getFkName($tableName, 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName($tableName, 'member_id', $tableMember, 'entity_id'),
            'member_id',
            $setup->getTable($tableMember),
            'entity_id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createMemberSchema(SchemaSetupInterface $setup)
    {
        $tableName = EntityTypeInterface::MEMBER_CODE;

        $table = $setup->getConnection()->newTable(
            $setup->getTable($tableName)
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ],
            'Member ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
            ],
            'Customer ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
            ],
            'Name'
        )->addColumn(
            'active',
            Table::TYPE_SMALLINT,
            null,
            ['default' => '1'],
            'Enabled'
        )->addIndex($setup->getIdxName($tableName, ['customer_id']), ['customer_id'])
        ->addForeignKey(
            $setup->getFkName($tableName, 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createSizeSchema(SchemaSetupInterface $setup)
    {
        $tableName = EntityTypeInterface::SIZE_CODE;

        $table = $setup->getConnection()->newTable($setup->getTable($tableName))->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Size ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
            ],
            'Name'
        )->addColumn(
            'chart_id',
            Table::TYPE_TEXT,
            100,
            [
                'nullable' => false,
            ],
            'Chart ID'
        )->addColumn(
            'dimension_id',
            Table::TYPE_TEXT,
            100,
            [
                'nullable' => false,
            ],
            'Dimension ID'
        )->addColumn(
            'position',
            Table::TYPE_INTEGER,
            10,
            ['default' => '0',],
            'Position'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Size Modification Time'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Size Creation Time'
        )->addIndex(
            $setup->getIdxName($tableName, ['chart_id']),
            ['chart_id']
        )->addIndex(
            $setup->getIdxName($tableName, ['dimension_id']),
            ['dimension_id']
        )->addForeignKey(
            $setup->getFkName($tableName, 'chart_id', EntityTypeInterface::CHART_CODE . '_entity', 'identifier'),
            'chart_id',
            $setup->getTable(EntityTypeInterface::CHART_CODE . '_entity'),
            'identifier',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName($tableName, 'dimension_id', EntityTypeInterface::DIMENSION_CODE . '_entity', 'identifier'),
            'dimension_id',
            $setup->getTable(EntityTypeInterface::DIMENSION_CODE . '_entity'),
            'identifier',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createChartSchema(SchemaSetupInterface $setup)
    {
        $tableName = EntityTypeInterface::CHART_CODE . '_entity';
        /**
         * Create entity Table
         */
        $table = $setup->getConnection()->newTable($setup->getTable($tableName))->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->setComment('Entity Table');
        $table->addColumn(
            'identifier',
            Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Identifier'
        )->addIndex(
            $setup->getIdxName($tableName, ['identifier']),
            ['identifier']
        );
        // Add more static attributes here...
        $table->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Update Time'
        );
        $setup->getConnection()->createTable($table);
        /** @var \Averun\SizeChart\Setup\EavTablesSetup $eavTablesSetup */
        $eavTablesSetup = $this->eavTablesSetupFactory->create(['setup' => $setup]);
        $eavTablesSetup->createEavTables(EntityTypeInterface::CHART_CODE);
    }
}
