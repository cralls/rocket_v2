<?php
namespace Rss\CustomDesigns\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    protected $urlBuilder;
    
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
        ) {
            $this->urlBuilder = $urlBuilder;
            parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        
        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['entity_id'])) {
                $editUrl = $this->urlBuilder->getUrl(
                    'customdesigns/request/edit',
                    ['entity_id' => $item['entity_id']]
                    );
                
                $item[$this->getData('name')] =
                '<a href="' . $editUrl . '">'
                    . __('Edit') .
                    '</a>';
            }
        }
        
        return $dataSource;
    }
}
