<?php
namespace VNS\Events\Ui\Component\DataProvider\SearchResult;

class Events extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}