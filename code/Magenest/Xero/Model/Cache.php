<?php
namespace Magenest\Xero\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;

class Cache
{
    protected $typeList;

    protected $pool;

    public function __construct(
        TypeListInterface $typeList,
        Pool $pool
    ) {
        $this->typeList = $typeList;
        $this->pool = $pool;
    }

    public function refreshCache()
    {
        $types = ['config','full_page'];
        foreach ($types as $type) {
            $this->typeList->cleanType($type);
        }
        foreach ($this->pool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
