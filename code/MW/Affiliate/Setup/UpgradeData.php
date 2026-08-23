<?php

namespace MW\Affiliate\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * UpgradeData constructor.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_customerFactory = $customerFactory;
    }
    
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
              $this->mergeCreditModule($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param $setup
     * @return $this
     */
    public function mergeCreditModule($setup)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('mw_credit_customer');
        // Check if the table already exists
        if ($connection->isTableExists($tableName) != true) {
            $customerCollection = $this->_customerFactory->create()->getCollection();
            if ($customerCollection->getSize() > 0) {
                foreach ($customerCollection as $customer) {
                    $setup->run("INSERT INTO {$setup->getTable('mw_credit_customer')} VALUES(".$customer->getId().", 0)");
                }
            }
        }

        return $this;
    }
}
