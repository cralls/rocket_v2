<?php
namespace Magenest\Xero\Model;

class RequestLog extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Magenest\Xero\Model\ResourceModel\RequestLog::class);
    }
}
