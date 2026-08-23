<?php
/**
 * InstallData
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Type setup factory
     *
     * @var TypeSetupFactory
     */
    protected $typeSetupFactory;

    /**
     * Type setup factory
     *
     * @var DimensionSetupFactory
     */
    protected $dimensionSetupFactory;

    /**
     * InstallData constructor.
     * @param CategorySetupFactory $categorySetupFactory
     * @param TypeSetupFactory $typeSetupFactory
     * @param DimensionSetupFactory $dimensionSetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        TypeSetupFactory $typeSetupFactory,
        DimensionSetupFactory $dimensionSetupFactory
    ) {
        $this->typeSetupFactory = $typeSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->dimensionSetupFactory = $dimensionSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var TypeSetup $typeSetup */
        $typeSetup = $this->typeSetupFactory->create(['setup' => $setup]);

        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        /** @var DimensionSetup $dimensionSetup */
        $dimensionSetup = $this->dimensionSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        $typeSetup->installEntities();
        $entities = $typeSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $typeSetup->addEntityType($entityName, $entity);
        }

        $categorySetup->installEntities();
        $entities = $categorySetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $categorySetup->addEntityType($entityName, $entity);
        }

        $dimensionSetup->installEntities();
        $entities = $dimensionSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $dimensionSetup->addEntityType($entityName, $entity);
        }

        $setup->endSetup();
    }
}
