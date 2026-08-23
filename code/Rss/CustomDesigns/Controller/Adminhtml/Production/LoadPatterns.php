<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Production;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;

class LoadPatterns extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::production_requests';

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
        $query = trim((string)$this->getRequest()->getParam('q', ''));
        $selectedCsv = trim((string)$this->getRequest()->getParam('selected_pattern_ids', ''));

        $selectedIds = [];
        if ($selectedCsv !== '') {
            foreach (explode(',', $selectedCsv) as $value) {
                $value = (int)trim($value);
                if ($value > 0) {
                    $selectedIds[$value] = $value;
                }
            }
        }

        $connection = $this->resource->getConnection();
        $patternsTable = $this->resource->getTableName('rss_custom_designs_patterns');

        $select = $connection->select()
            ->from($patternsTable, ['pattern_id', 'pattern_number', 'description']);

        if ($query !== '') {
            $like = '%' . $query . '%';
            $select->where(
                '(pattern_number LIKE ? OR description LIKE ?)',
                $like
            );
        }

        if ($selectedIds) {
            $select->orWhere('pattern_id IN (?)', array_values($selectedIds));
        }

        $select->order('pattern_number ASC');
        $select->limit(200);

        $items = $connection->fetchAll($select);

        return $result->setData([
            'error' => false,
            'items' => $items
        ]);
    }
}