<?php

namespace Rss\CustomDesigns\Controller\Adminhtml\Patterns;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;

class LoadBoms extends Action
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
        $patternId = (int)$this->getRequest()->getParam('pattern_id');

        if (!$patternId) {
            return $result->setData([
                'error' => true,
                'message' => __('Missing pattern_id.')
            ]);
        }

        $connection = $this->resource->getConnection();
        $patternsTable = $this->resource->getTableName('rss_custom_designs_patterns');
        $bomTable = $this->resource->getTableName('rss_custom_designs_bom');
        $bridgeTable = $this->resource->getTableName('rss_custom_designs_pattern_bom');

        $pattern = $connection->fetchRow(
            $connection->select()
                ->from($patternsTable, ['pattern_id', 'pattern_number'])
                ->where('pattern_id = ?', $patternId)
        );

        if (!$pattern) {
            return $result->setData([
                'error' => true,
                'message' => __('Pattern not found.')
            ]);
        }

        $materials = $connection->fetchAll(
            $connection->select()
                ->from($bomTable, ['material_id', 'material_code', 'material_name', 'material_type'])
                ->where('is_active = ?', 1)
                ->order(['material_name ASC'])
        );

        $assignedIds = $connection->fetchCol(
            $connection->select()
                ->from($bridgeTable, ['material_id'])
                ->where('pattern_id = ?', $patternId)
                ->order(['sort_order ASC', 'material_id ASC'])
        );

        return $result->setData([
            'error' => false,
            'pattern_id' => (int)$pattern['pattern_id'],
            'pattern_number' => (string)$pattern['pattern_number'],
            'assigned_ids' => array_map('intval', $assignedIds),
            'materials' => $materials
        ]);
    }
}