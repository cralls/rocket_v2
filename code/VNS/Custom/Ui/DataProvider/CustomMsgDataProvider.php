<?php
namespace VNS\Custom\Ui\DataProvider;

class CustomMsgDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}