<?php

namespace Rss\CustomDesigns\Controller\Adminhtml\Bom;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';

    protected $resource;

    public function __construct(
        Action\Context $context,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->resource = $resource;
    }

    public function execute()
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('rss_custom_designs_bom');

        try {
            $baseName = 'New Material';
            $materialName = $baseName;
            $counter = 1;

            while ($connection->fetchOne(
                $connection->select()
                    ->from($table, ['material_id'])
                    ->where('material_name = ?', $materialName)
            )) {
                $counter++;
                $materialName = $baseName . ' ' . $counter;
            }

            $connection->insert($table, [
                'material_code' => null,
                'material_name' => $materialName,
                'material_name_cn' => null,
                'material_type' => null,
                'description' => null,
                'is_active' => 1
            ]);

            $this->messageManager->addSuccessMessage(__('Blank BOM material created.'));
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_redirect('customdesigns/bom/index');
    }
}