<?php
/**
 * UpgradeData
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Setup;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ChartSetupFactory
     */
    protected $chartSetupFactory;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeData constructor
     *
     * @param ChartSetupFactory $chartSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(ChartSetupFactory $chartSetupFactory, EavSetupFactory $eavSetupFactory)
    {
        $this->chartSetupFactory = $chartSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->installChart($setup, $context);
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->addChartAttribute2Product($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->addChartAttribute2Category($setup);
        }
//        if (version_compare($context->getVersion(), '1.0.8', '<')) {
//            $this->addFieldWeight2Dimension($setup);
//        }
//        if (version_compare($context->getVersion(), '1.0.9', '<')) {
//            $this->addFieldHeight2Dimension($setup);
//        }
        if (version_compare($context->getVersion(), '1.0.11', '<')) {
            $this->addFieldLengthType2Dimension($setup);
        }
        $setup->endSetup();
    }

    private function installChart(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var ChartSetup $chartSetup */
        $chartSetup = $this->chartSetupFactory->create(['setup' => $setup]);
        $chartSetup->installEntities();
        $entities = $chartSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $chartSetup->addEntityType($entityName, $entity);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function addChartAttribute2Category(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Category::ENTITY,
            'ave_size_chart',
            [
                'group'        => 'Content',
                'label'        => 'Size Chart',
                'input'        => 'select',
                'type'         => 'varchar',
                'source'       => 'Averun\SizeChart\Model\Attribute\Source\Chart',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required'     => 0,
                'unique'       => 0,
                'user_defined' => 1,
            ]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function addChartAttribute2Product(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'ave_size_chart',
            [
                'group'        => 'General',
                'label'        => 'Size Chart',
                'input'        => 'select',
                'type'         => 'varchar',
                'source'       => 'Averun\SizeChart\Model\Attribute\Source\Chart',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required'     => 0,
                'unique'       => 0,
                'user_defined' => 1,
            ]
        );
    }

//    /**
//     * @param ModuleDataSetupInterface $setup
//     */
//    protected function addFieldWeight2Dimension(ModuleDataSetupInterface $setup)
//    {
//        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
//        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
//        $eavSetup->addAttribute(
//            EntityTypeInterface::DIMENSION_CODE,
//            'is_weight',
//            [
//                'group'        => 'General',
//                'label'        => 'Weight',
//                'input'        => 'select',
//                'type'         => 'int',
//                'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
//                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
//                'required'     => 0,
//                'unique'       => 0,
//                'user_defined' => 1
//            ]
//        );
//    }
//
//    /**
//     * @param ModuleDataSetupInterface $setup
//     */
//    protected function addFieldHeight2Dimension(ModuleDataSetupInterface $setup)
//    {
//        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
//        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
//        $eavSetup->addAttribute(
//            EntityTypeInterface::DIMENSION_CODE,
//            'is_height',
//            [
//                'group'        => 'General',
//                'label'        => 'Height',
//                'input'        => 'select',
//                'type'         => 'int',
//                'source'       => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
//                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
//                'required'     => 0,
//                'unique'       => 0,
//                'user_defined' => 1
//            ]
//        );
//    }
    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function addFieldLengthType2Dimension(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            EntityTypeInterface::DIMENSION_CODE,
            'length_type',
            [
                'group'        => 'General',
                'label'        => 'Calculation type',
                'input'        => 'select',
                'type'         => 'varchar',
                'source'       => 'Averun\SizeChart\Model\Attribute\Source\LengthTypes',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'required'     => 0,
                'unique'       => 0,
                'user_defined' => 1
            ]
        );
    }
}
