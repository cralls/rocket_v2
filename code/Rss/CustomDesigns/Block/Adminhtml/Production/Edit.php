<?php
namespace Rss\CustomDesigns\Block\Adminhtml\Production;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;

class Edit extends Template
{
    protected $registry;
    protected $resource;
    protected $formKey;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        ResourceConnection $resource,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->resource = $resource;
        $this->formKey = $formKey;
    }

    public function getModel()
    {
        return $this->registry->registry('rss_production_request');
    }

    public function getFormKeyValue()
    {
        return $this->formKey->getFormKey();
    }

    public function getSelectedPatternIds()
    {
        $model = $this->getModel();
        if (!$model || !$model->getId()) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $bridgeTable = $this->resource->getTableName('rss_production_request_pattern');

        $ids = $connection->fetchCol(
            $connection->select()
                ->from($bridgeTable, ['pattern_id'])
                ->where('production_request_id = ?', (int)$model->getId())
                ->order(['is_primary DESC', 'sort_order ASC', 'pattern_id ASC'])
        );

        return array_map('intval', $ids);
    }

    public function getSelectedPatternIdsCsv()
    {
        return implode(',', $this->getSelectedPatternIds());
    }

    public function getSelectedPatterns()
    {
        $ids = $this->getSelectedPatternIds();
        if (!$ids) {
            return [];
        }

        $connection = $this->resource->getConnection();
        $patternsTable = $this->resource->getTableName('rss_custom_designs_patterns');

        $rows = $connection->fetchAll(
            $connection->select()
                ->from($patternsTable, ['pattern_id', 'pattern_number', 'description'])
                ->where('pattern_id IN (?)', $ids)
                ->order('pattern_number ASC')
        );

        $lookup = [];
        foreach ($rows as $row) {
            $lookup[(int)$row['pattern_id']] = $row;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($lookup[$id])) {
                $ordered[] = $lookup[$id];
            }
        }

        return $ordered;
    }

    public function getLoadPatternsUrl()
    {
        return $this->getUrl('customdesigns/production/loadPatterns');
    }
}
