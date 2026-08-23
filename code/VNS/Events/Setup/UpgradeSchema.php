<?php
declare(strict_types=1);

namespace VNS\Events\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addNewFieldsToEventsTable($setup);
        }
        
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->addEventLinkToEventsTable($setup);
        }

        $setup->endSetup();
    }

    private function addEventLinkToEventsTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('vns_events');
        
        $connection->addColumn(
            $tableName,
            'event_link',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 512,
                'nullable' => true,
                'comment' => 'Event Link'
            ]
            );
    }
    
    private function addNewFieldsToEventsTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('vns_events');

        $connection->addColumn(
            $tableName,
            'image_url_two',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 512,
                'nullable' => true,
                'comment' => 'Second Image URL'
            ]
        );

        $connection->addColumn(
            $tableName,
            'image_url_three',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 512,
                'nullable' => true,
                'comment' => 'Third Image URL'
            ]
        );

        $connection->addColumn(
            $tableName,
            'video_url',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 512,
                'nullable' => true,
                'comment' => 'Video URL'
            ]
        );

        $connection->addColumn(
            $tableName,
            'time',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 512,
                'nullable' => true,
                'comment' => 'Event Time'
            ]
        );

        $connection->addColumn(
            $tableName,
            'description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Event Description'
            ]
        );
    }
}
