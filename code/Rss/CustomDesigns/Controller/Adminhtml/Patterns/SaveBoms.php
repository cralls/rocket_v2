<?php

namespace Rss\CustomDesigns\Controller\Adminhtml\Patterns;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;

class SaveBoms extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';

    protected $jsonFactory;
    protected $resource;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->resource = $resource;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $request = $this->getRequest();

        $patternId = (int)$request->getParam('pattern_id');
        $materialIds = $request->getParam('material_ids', []);

        if (!$patternId) {
            return $result->setData([
                'error' => true,
                'message' => __('Missing pattern_id.')
            ]);
        }

        if (!is_array($materialIds)) {
            $materialIds = [$materialIds];
        }

        $normalized = [];
        foreach ($materialIds as $materialId) {
            $materialId = (int)$materialId;
            if ($materialId > 0) {
                $normalized[$materialId] = $materialId;
            }
        }
        $materialIds = array_values($normalized);

        $connection = $this->resource->getConnection();
        $bridgeTable = $this->resource->getTableName('rss_custom_designs_pattern_bom');
        $bomTable = $this->resource->getTableName('rss_custom_designs_bom');

        try {
            $connection->beginTransaction();

            $connection->delete($bridgeTable, ['pattern_id = ?' => $patternId]);

            $sortOrder = 10;
            foreach ($materialIds as $materialId) {
                $exists = $connection->fetchOne(
                    $connection->select()
                        ->from($bomTable, ['material_id'])
                        ->where('material_id = ?', $materialId)
                );

                if (!$exists) {
                    continue;
                }

                $connection->insert($bridgeTable, [
                    'pattern_id' => $patternId,
                    'material_id' => $materialId,
                    'sort_order' => $sortOrder
                ]);

                $sortOrder += 10;
            }

            $connection->commit();

            return $result->setData([
                'error' => false,
                'message' => __('BOMs saved successfully.')
            ]);
        } catch (\Exception $e) {
            $connection->rollBack();

            return $result->setData([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}