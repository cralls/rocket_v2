<?php

namespace Rss\CustomDesigns\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class UrlRenderer extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        
        foreach ($dataSource['data']['items'] as &$item) {
            
            if (empty($item['http_address'])) {
                continue;
            }
            
            $url = trim($item['http_address']);
            
                
                $item[$this->getData('name')] =
                '<a href="' . $url . '" target="_blank" onclick="event.stopPropagation();"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 3h3v3"/>
        <path d="M11 13L21 3"/>
        <path d="M21 14v7H3V3h7"/>
    </svg></a>';
        }
        
        return $dataSource;
    }
}
