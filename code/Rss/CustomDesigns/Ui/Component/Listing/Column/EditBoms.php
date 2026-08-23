<?php

namespace Rss\CustomDesigns\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class EditBoms extends Column
{
    protected $escaper;
    
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        array $components = [],
        array $data = []
        ) {
            $this->escaper = $escaper;
            parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }
        
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['pattern_id'])) {
                continue;
            }
            
            $patternId = (int)$item['pattern_id'];
            $patternNumber = isset($item['pattern_number']) ? $this->escaper->escapeHtmlAttr($item['pattern_number']) : '';
            
            $item[$this->getData('name')] =
            '<button type="button"'
                . ' class="action-default scalable rss-edit-boms"'
                    . ' data-pattern-id="' . $patternId . '"'
                        . ' data-pattern-number="' . $patternNumber . '"'
                            . ' style="padding:0;border:0;background:none;color:#006bb4;text-decoration:underline;cursor:pointer;">'
                                . __('Edit BOMs')
                                . '</button>';
        }
        
        return $dataSource;
    }
}