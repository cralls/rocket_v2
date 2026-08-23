<?php

namespace Rss\CustomDesigns\Controller\Adminhtml\Bom;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
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
        $resultJson = $this->jsonFactory->create();
        $request = $this->getRequest();

        if (!$request->getParam('isAjax')) {
            return $resultJson->setData([
                'messages' => [__('Invalid request.')],
                'error' => true
            ]);
        }

        $items = $request->getParam('items', []);
        if (!count($items)) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true
            ]);
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('rss_custom_designs_bom');

        $messages = [];
        $error = false;

        foreach ($items as $materialId => $data) {
            try {
                $materialId = (int)$materialId;
                if (!$materialId) {
                    throw new LocalizedException(__('Missing material_id.'));
                }

                $existing = $connection->fetchRow(
                    $connection->select()
                        ->from($table)
                        ->where('material_id = ?', $materialId)
                );

                if (!$existing) {
                    throw new LocalizedException(__('Material ID %1 no longer exists.', $materialId));
                }

                $saveData = [
                    'material_code' => isset($data['material_code']) ? trim((string)$data['material_code']) : $existing['material_code'],
                    'material_name' => isset($data['material_name']) ? trim((string)$data['material_name']) : $existing['material_name'],
                    'material_name_cn' => isset($data['material_name_cn']) ? trim((string)$data['material_name_cn']) : $existing['material_name_cn'],
                    'material_type' => isset($data['material_type']) ? trim((string)$data['material_type']) : $existing['material_type'],
                    'description' => isset($data['description']) ? trim((string)$data['description']) : $existing['description'],
                    'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : (int)$existing['is_active'],
                ];

                if ($saveData['material_name'] === '') {
                    throw new LocalizedException(__('Material Name is required for ID %1.', $materialId));
                }

                $dupNameSelect = $connection->select()
                    ->from($table, ['material_id'])
                    ->where('material_name = ?', $saveData['material_name'])
                    ->where('material_id != ?', $materialId);

                $dupNameId = $connection->fetchOne($dupNameSelect);
                if ($dupNameId) {
                    throw new LocalizedException(__('Duplicate Material Name "%1" already exists on ID %2.', $saveData['material_name'], $dupNameId));
                }

                if ($saveData['material_code'] !== '') {
                    $dupCodeSelect = $connection->select()
                        ->from($table, ['material_id'])
                        ->where('material_code = ?', $saveData['material_code'])
                        ->where('material_id != ?', $materialId);

                    $dupCodeId = $connection->fetchOne($dupCodeSelect);
                    if ($dupCodeId) {
                        throw new LocalizedException(__('Duplicate Material Code "%1" already exists on ID %2.', $saveData['material_code'], $dupCodeId));
                    }
                }

                $connection->update(
                    $table,
                    $saveData,
                    ['material_id = ?' => $materialId]
                );
            } catch (\Exception $e) {
                $messages[] = '[ID ' . $materialId . '] ' . $e->getMessage();
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}