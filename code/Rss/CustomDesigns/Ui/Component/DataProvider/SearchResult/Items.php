<?php
namespace Rss\CustomDesigns\Ui\Component\DataProvider\SearchResult;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Items extends SearchResult
{
    protected function _initSelect()
    {
        parent::_initSelect();
        // You can add join/filter logic here later if needed.
        return $this;
    }
}
