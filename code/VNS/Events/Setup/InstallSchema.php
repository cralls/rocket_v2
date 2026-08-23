<?php
namespace VNS\Events\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('vns_events')
        )->addColumn(
            'event_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Event ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            'Event Name'
        )->addColumn(
            'image_url',
            Table::TYPE_TEXT,
            512,
            ['nullable' => false],
            'Image URL'
        )->addColumn(
            'from_date',
            Table::TYPE_DATE,
            null,
            ['nullable' => false],
            'Event Start Date'
        )->addColumn(
            'to_date',
            Table::TYPE_DATE,
            null,
            ['nullable' => false],
            'Event End Date'
        )->addColumn(
            'location',
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            'Event Location'
        )->addColumn(
            'age_range',
            Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Age Range'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            'Event Type'
        )->setComment(
            'Events Table'
        );

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
