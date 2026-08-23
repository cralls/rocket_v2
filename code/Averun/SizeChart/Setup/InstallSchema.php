<?php
/**
 * installSchema.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var EavTablesSetupFactory
     */
    protected $eavTablesSetupFactory;

    /**
     * InstallSchema constructor.
     * @param \Averun\SizeChart\Setup\EavTablesSetupFactory $eavTablesSetupFactory
     */
    public function __construct(EavTablesSetupFactory $eavTablesSetupFactory)
    {
        $this->eavTablesSetupFactory = $eavTablesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) //@codingStandardsIgnoreLine
    {
        $setup->startSetup();

        $this->installTablesByType($setup, EntityTypeInterface::TYPE_CODE);
        $this->installTablesByType($setup, EntityTypeInterface::CATEGORY_CODE);
        $this->installTablesByType($setup, EntityTypeInterface::DIMENSION_CODE);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param $entityCode
     * @return EavTablesSetup
     */
    private function installTablesByType(SchemaSetupInterface $setup, $entityCode)
    {
        $tableName = $entityCode . '_entity';
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
        $eavTablesSetup->createEavTables($entityCode);
        return $eavTablesSetup;
    }
}
