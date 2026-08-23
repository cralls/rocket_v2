<?php
namespace MageArray\OrderAttachments\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    // @codingStandardsIgnoreStart
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
    */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // @codingStandardsIgnoreEnd
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('magearray_order_attachments')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            15,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true],
            'ID'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true , 'unsigned' => true ],
            'Order ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            11,
            [ 'nullable' => true , 'unsigned' => true ],
            'Customer Id'
        )->addColumn(
            'file_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false ],
            'File Name'
        )->addColumn(
            'file_path',
            Table::TYPE_TEXT,
            1000,
            ['nullable' => false ],
            'File Path'
        )
            ->addColumn(
                'comment',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false ],
                'Comment'
            )->addColumn(
                'visible_customer_account',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => '1',
                ],
                'Is visible in customer account'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [ ],
                'Update at'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [ ],
                'Creation Time'
            )->setComment(
                'Order Attachments'
            );
        $table->engine = 'InnoDB';
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
