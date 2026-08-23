<?php

namespace VNS\Ups\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            // Check if the version is less than 1.0.1 (or your module's specific version where this change is introduced)

            $tableName = $setup->getTable('sales_shipment_track');
            
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();

                // Add the 'delivered' column
                $connection->addColumn(
                    $tableName,
                    'delivered',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Delivered Status'
                    ]
                );
                $connection->addColumn(
                    $tableName,
                    'out_for_delivery',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Out for Delivery Status'
                    ]
                    );
            }
        }
    }
}

