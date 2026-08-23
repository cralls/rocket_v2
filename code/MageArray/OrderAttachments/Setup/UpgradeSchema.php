<?php
namespace MageArray\OrderAttachments\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package MageArray\OrderAttachments\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('magearray_file_attachments')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                15,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'ID'
            )->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => true, 'unsigned' => true],
                'Quote ID'
            )->addColumn(
                'file_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'unsigned' => true],
                'Customer Id'
            );
            $table->engine = 'InnoDB';
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
