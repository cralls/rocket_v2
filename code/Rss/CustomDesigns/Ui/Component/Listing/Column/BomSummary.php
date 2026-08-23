<?php

namespace Rss\CustomDesigns\Ui\Component\Listing\Column;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BomSummary extends Column
{
    protected $resource;
    protected $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resource,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->resource = $resource;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $patternIds = [];
        foreach ($dataSource['data']['items'] as $item) {
            if (!empty($item['pattern_id'])) {
                $patternIds[] = (int)$item['pattern_id'];
            }
        }

        if (!$patternIds) {
            return $dataSource;
        }

        $connection = $this->resource->getConnection();
        $bridgeTable = $this->resource->getTableName('rss_custom_designs_pattern_bom');
        $bomTable = $this->resource->getTableName('rss_custom_designs_bom');

        $select = $connection->select()
            ->from(['pb' => $bridgeTable], ['pattern_id', 'sort_order'])
            ->joinLeft(['b' => $bomTable], 'pb.material_id = b.material_id', ['material_name'])
            ->where('pb.pattern_id IN (?)', $patternIds)
            ->order(['pb.pattern_id ASC', 'pb.sort_order ASC', 'b.material_name ASC']);

        $rows = $connection->fetchAll($select);

        $grouped = [];
        foreach ($rows as $row) {
            $pid = (int)$row['pattern_id'];
            $name = trim((string)$row['material_name']);
            if ($name !== '') {
                $grouped[$pid][] = $name;
            }
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $patternId = isset($item['pattern_id']) ? (int)$item['pattern_id'] : 0;
            $names = $grouped[$patternId] ?? [];

            if (!$names) {
                $item[$this->getData('name')] = '<span style="color:#999;">None</span>';
                continue;
            }

            $display = array_slice($names, 0, 3);
            $escaped = array_map([$this->escaper, 'escapeHtml'], $display);
            $text = implode(', ', $escaped);

            $remaining = count($names) - count($display);
            if ($remaining > 0) {
                $text .= ' <span style="color:#666;">(+' . (int)$remaining . ' more)</span>';
            }

            $item[$this->getData('name')] = $text;
        }

        return $dataSource;
    }
}