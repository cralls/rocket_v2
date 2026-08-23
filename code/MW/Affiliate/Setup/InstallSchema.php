<?php

namespace MW\Affiliate\Setup;

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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        // Create mw_affiliate_banner table
        $tableName = $setup->getTable('mw_affiliate_banner');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'banner_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Banner ID'
                )
                ->addColumn(
                    'title_banner',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Banner Title'
                )
                ->addColumn(
                    'link_banner',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Banner Link'
                )
                ->addColumn(
                    'width',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Width'
                )
                ->addColumn(
                    'height',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Height'
                )
                ->addColumn(
                    'image_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Image Name'
                )
                ->addColumn(
                    'group_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Group ID'
                )
                ->addColumn(
                    'store_view',
                    Table::TYPE_TEXT,
                    '255',
                    ['nullable' => false, 'default' => '0'],
                    'Store View'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate Banner Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_banner_member table
        $tableName = $setup->getTable('mw_affiliate_banner_member');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'banner_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Banner ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->setComment('Affiliate Banner Member Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_customers table
        $tableName = $setup->getTable('mw_affiliate_customers');
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
                    'active',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Active'
                )
                ->addColumn(
                    'payment_gateway',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Gateway'
                )
                ->addColumn(
                    'payment_email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Email'
                )
                ->addColumn(
                    'auto_withdrawn',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Auto Withdrawn'
                )
                ->addColumn(
                    'withdrawn_level',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Withdrawn Level'
                )
                ->addColumn(
                    'reserve_level',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Reserve Level'
                )
                ->addColumn(
                    'total_commission',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Commission'
                )
                ->addColumn(
                    'total_paid',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Paid'
                )
                ->addColumn(
                    'referral_code',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Referral Code'
                )
                ->addColumn(
                    'bank_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Bank Name'
                )
                ->addColumn(
                    'name_account',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name Account'
                )
                ->addColumn(
                    'bank_country',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Bank Country'
                )
                ->addColumn(
                    'swift_bic',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Swift Bic'
                )
                ->addColumn(
                    'account_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Account Number'
                )
                ->addColumn(
                    're_account_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Re-Account Number'
                )
                ->addColumn(
                    'referral_site',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Referral Site'
                )
                ->addColumn(
                    'customer_invited',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer Invited'
                )
                ->addColumn(
                    'invitation_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Invitation Type'
                )
                ->addColumn(
                    'link_click_id_pivot',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => '0'],
                    'Link Click ID Pivot'
                )
                ->addColumn(
                    'customer_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Customer Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate Customers Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_history table
        $tableName = $setup->getTable('mw_affiliate_history');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'history_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'History ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Product ID'
                )
                ->addColumn(
                    'program_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Program ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Order ID'
                )
                ->addColumn(
                    'total_amount',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Amount'
                )
                ->addColumn(
                    'history_commission',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'History Commission'
                )
                ->addColumn(
                    'history_discount',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'History Discount'
                )
                ->addColumn(
                    'transaction_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Transaction Time'
                )
                ->addColumn(
                    'customer_invited',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer Invited'
                )
                ->addColumn(
                    'invitation_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Invitation Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate History Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_invitation table
        $tableName = $setup->getTable('mw_affiliate_invitation');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'invitation_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Invitation ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Email'
                )
                ->addColumn(
                    'ip',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'IP'
                )
                ->addColumn(
                    'count_click_link',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Count Click Link'
                )
                ->addColumn(
                    'count_register',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Count Register'
                )
                ->addColumn(
                    'count_purchase',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Count Purchase'
                )
                ->addColumn(
                    'referral_from',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Referral From'
                )
                ->addColumn(
                    'referral_from_domain',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Referral From Domain'
                )
                ->addColumn(
                    'referral_to',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Referral To'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Order ID'
                )
                ->addColumn(
                    'invitation_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Invitation Type'
                )
                ->addColumn(
                    'invitation_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Invitation Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    'commission',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Commission'
                )
                ->addColumn(
                    'count_subscribe',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Count Subscribe'
                )
                ->setComment('Affiliate Invitation Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_program table
        $tableName = $setup->getTable('mw_affiliate_program');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'program_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Program ID'
                )
                ->addColumn(
                    'program_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Program Name'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Description'
                )
                ->addColumn(
                    'conditions_serialized',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Conditions Serialized'
                )
                ->addColumn(
                    'actions_serialized',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Actions Serialized'
                )
                ->addColumn(
                    'start_date',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Start Date'
                )
                ->addColumn(
                    'end_date',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'End Date'
                )
                ->addColumn(
                    'commission',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Commission'
                )
                ->addColumn(
                    'discount',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Discount'
                )
                ->addColumn(
                    'total_members',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Total Members'
                )
                ->addColumn(
                    'total_commission',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Commission'
                )
                ->addColumn(
                    'program_position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Program Position'
                )
                ->addColumn(
                    'store_view',
                    Table::TYPE_TEXT,
                    '255',
                    ['nullable' => false, 'default' => '0'],
                    'Store View'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate Program Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_transaction table
        $tableName = $setup->getTable('mw_affiliate_transaction');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'history_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'History ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Order ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'total_commission',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Commission'
                )
                ->addColumn(
                    'total_discount',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Total Discount'
                )
                ->addColumn(
                    'transaction_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Transaction Time'
                )
                ->addColumn(
                    'commission_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => '7'],
                    'Commission Type'
                )
                ->addColumn(
                    'show_customer_invited',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Show Customer Invited'
                )
                ->addColumn(
                    'customer_invited',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer Invited'
                )
                ->addColumn(
                    'invitation_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Invitation Type'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate Transaction Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_withdrawn table
        $tableName = $setup->getTable('mw_affiliate_withdrawn');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'withdrawn_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Withdrawn ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->addColumn(
                    'payment_gateway',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Gateway'
                )
                ->addColumn(
                    'payment_email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Email'
                )
                ->addColumn(
                    'bank_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Bank Name'
                )
                ->addColumn(
                    'name_account',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name Account'
                )
                ->addColumn(
                    'bank_country',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Bank Country'
                )
                ->addColumn(
                    'swift_bic',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Swift Bic'
                )
                ->addColumn(
                    'account_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Account Number'
                )
                ->addColumn(
                    're_account_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Re-Account Number'
                )
                ->addColumn(
                    'withdrawn_amount',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Withdrawn Amount'
                )
                ->addColumn(
                    'fee',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Fee'
                )
                ->addColumn(
                    'amount_receive',
                    Table::TYPE_DECIMAL,
                    '15,2',
                    ['nullable' => false, 'default' => '0.00'],
                    'Amount Receive'
                )
                ->addColumn(
                    'withdrawn_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Withdrawn Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Affiliate Withdrawn Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_group table
        $tableName = $setup->getTable('mw_affiliate_group');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'group_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Group ID'
                )
                ->addColumn(
                    'group_name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Group Name'
                )
                ->addColumn(
                    'limit_day',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Limit Day'
                )
                ->addColumn(
                    'limit_order',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Limit Order'
                )
                ->addColumn(
                    'limit_commission',
                    Table::TYPE_DECIMAL,
                    '15,0',
                    ['nullable' => false, 'default' => '0'],
                    'Limit Commission'
                )
                ->setComment('Affiliate Group Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_group_member table
        $tableName = $setup->getTable('mw_affiliate_group_member');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'group_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Group ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Customer ID'
                )
                ->setComment('Affiliate Group Member Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_group_program table
        $tableName = $setup->getTable('mw_affiliate_group_program');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'group_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Group ID'
                )
                ->addColumn(
                    'program_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Program ID'
                )
                ->setComment('Affiliate Group Program Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Create mw_affiliate_website_member table
        $tableName = $setup->getTable('mw_affiliate_website_member');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $table = $connection->newTable($tableName)
                ->addColumn(
                    'affiliate_website_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Affiliate Website ID'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Customer ID'
                )
                ->addColumn(
                    'domain_name',
                    Table::TYPE_TEXT,
                    '255',
                    ['nullable' => false, 'default' => ''],
                    'Domain Name'
                )
                ->addColumn(
                    'verified_key',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Verified Key'
                )
                ->addColumn(
                    'is_verified',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Is Verified'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Status'
                )
                ->addColumn(
                    'created_time',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true, 'default' => 'CURRENT_TIMESTAMP'],
                    'Created Time'
                )
                ->addIndex('UNIQUE', 'domain_name')
                ->setComment('Affiliate Website Member Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $connection->createTable($table);
        }

        // Add affiliate discount to quote_item table
        $connection->addColumn(
            $setup->getTable('quote_item'),
            'mw_affiliate_discount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Affiliate Discount'
            ]
        );
        $connection->addColumn(
            $setup->getTable('quote_item'),
            'mw_credit_discount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => true,
                'length' => '12,4',
                'comment' => 'Credit Discount'
            ]
        );

        $setup->endSetup();
    }
}
