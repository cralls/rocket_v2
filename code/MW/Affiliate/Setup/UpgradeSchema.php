<?php

namespace MW\Affiliate\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->mergeCreditModule($setup);

        }
    }

    /**
     * @param $setup
     */
    public function mergeCreditModule($setup)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        // Create mw_credit_history table
        $tableName = $setup->getTable('mw_credit_history');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'credit_history_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Credit History ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'type_transaction',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Type Transaction'
                )
                ->addColumn(
                    'transaction_detail',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Transaction Detail'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Amount'
                )
                ->addColumn(
                    'beginning_transaction',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Beginning Transaction'
                )
                ->addColumn(
                    'end_transaction',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'End Transaction'
                )
                ->addColumn(
                    'created_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Created Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => '0'],
                    'Status'
                )
                ->setComment('Credit History Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_credit_customer table
        $tableName = $setup->getTable('mw_credit_customer');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'primary' => true, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'credit',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Credit'
                )
                ->setComment('Credit Customer Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_credit_order table
        $tableName = $setup->getTable('mw_credit_order');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    '255',
                    ['nullable' => false, 'primary' => true, 'default' => ''],
                    'Order ID'
                )
                ->addColumn(
                    'credit',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Credit'
                )
                ->addColumn(
                    'affiliate',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Affiliate'
                )
                ->setComment('Credit Order Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }
    }
}
