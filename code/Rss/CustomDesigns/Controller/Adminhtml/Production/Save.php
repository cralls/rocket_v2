<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Production;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\Redirect;

class Save extends Action
{
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $post = $request->getPostValue();

        if (!$post) {
            return $resultRedirect->setPath('*/*/listing');
        }

        $objectManager = ObjectManager::getInstance();
        /** @var ResourceConnection $resource */
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();

        $mainTable = $resource->getTableName('rss_production_requests');
        $bridgeTable = $resource->getTableName('rss_production_request_pattern');

        $entityId = (int)$request->getParam('entity_id');
        $selectedPatternIdsRaw = (string)$request->getParam('selected_pattern_ids', '');

        $selectedPatternIds = array_values(array_unique(array_filter(array_map('intval', explode(',', $selectedPatternIdsRaw)))));

        try {
            $connection->beginTransaction();

            $tableColumns = array_keys($connection->describeTable($mainTable));
            $excludedKeys = [
                'entity_id',
                'selected_pattern_ids',
                'form_key',
                'key',
                'back'
            ];

            $saveData = [];
            foreach ($post as $field => $value) {
                if (in_array($field, $excludedKeys, true)) {
                    continue;
                }

                if (!in_array($field, $tableColumns, true)) {
                    continue;
                }

                if (is_array($value)) {
                    $saveData[$field] = implode(',', $value);
                    continue;
                }

                $saveData[$field] = $value;
            }

            if (in_array('updated_at', $tableColumns, true)) {
                $saveData['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
            }

            if ($entityId > 0) {
                $connection->update(
                    $mainTable,
                    $saveData,
                    ['entity_id = ?' => $entityId]
                );
                $productionRequestId = $entityId;
            } else {
                if (in_array('created_at', $tableColumns, true) && empty($saveData['created_at'])) {
                    $saveData['created_at'] = (new \DateTime())->format('Y-m-d H:i:s');
                }

                $connection->insert($mainTable, $saveData);
                $productionRequestId = (int)$connection->lastInsertId($mainTable);
            }

            $connection->delete(
                $bridgeTable,
                ['production_request_id = ?' => (int)$productionRequestId]
            );

            if ($selectedPatternIds) {
                $bridgeColumns = array_keys($connection->describeTable($bridgeTable));
                $rows = [];
                $sortOrder = 0;

                foreach ($selectedPatternIds as $patternId) {
                    if ($patternId <= 0) {
                        continue;
                    }

                    $row = [];

                    if (in_array('production_request_id', $bridgeColumns, true)) {
                        $row['production_request_id'] = (int)$productionRequestId;
                    }
                    if (in_array('pattern_id', $bridgeColumns, true)) {
                        $row['pattern_id'] = (int)$patternId;
                    }
                    if (in_array('is_primary', $bridgeColumns, true)) {
                        $row['is_primary'] = ($sortOrder === 0 ? 1 : 0);
                    }
                    if (in_array('sort_order', $bridgeColumns, true)) {
                        $row['sort_order'] = $sortOrder;
                    }
                    if (in_array('created_at', $bridgeColumns, true)) {
                        $row['created_at'] = (new \DateTime())->format('Y-m-d H:i:s');
                    }
                    if (in_array('updated_at', $bridgeColumns, true)) {
                        $row['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');
                    }

                    if (!empty($row)) {
                        $rows[] = $row;
                    }

                    $sortOrder++;
                }

                if ($rows) {
                    $connection->insertMultiple($bridgeTable, $rows);
                }
            }

            $connection->commit();
            $this->messageManager->addSuccessMessage(__('The production request has been saved.'));

            if ($request->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $productionRequestId, '_current' => true]);
            }

            return $resultRedirect->setPath('*/*/listing');
        } catch (\Throwable $e) {
            if ($connection->getTransactionLevel() > 0) {
                $connection->rollBack();
            }

            $this->messageManager->addErrorMessage(__('Unable to save the production request: %1', $e->getMessage()));

            if ($entityId > 0) {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $entityId, '_current' => true]);
            }

            return $resultRedirect->setPath('*/*/listing');
        }
    }
}
