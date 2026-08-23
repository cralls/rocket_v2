<?php

namespace MW\Affiliate\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Get mw_affiliate_group table
        $tableName = $setup->getTable('mw_affiliate_group');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->insert(
                $tableName,
                [
                    'group_id' => 1,
                    'group_name' => 'default group'
                ]
            );
        }

        $setup->endSetup();
    }
}
