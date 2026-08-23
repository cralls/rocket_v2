<?php
namespace Magenest\Xero\Model;

use Magenest\Xero\Helper\Signature;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\ScopeInterface;

class CoreConfig extends \Magento\Framework\Model\AbstractModel
{
    /**
     *  Init
     */
    protected function _construct()
    {
        $this->_init(\Magenest\Xero\Model\ResourceModel\CoreConfig::class);
    }

    /**
     * @param $path
     * @param $scope
     * @param $scopeId
     * @return null | int
     */
    public function getConfigValueByScope($path, $scope, $scopeId)
    {
        if ($scope != ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            && $scope != ScopeInterface::SCOPE_STORES
            && $scope != ScopeInterface::SCOPE_WEBSITES
        ) {
            return null;
        }
        $collection = $this->getCollection()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->addFieldToFilter('path', $path);
        return $collection->getFirstItem()->getValue() ? : 0;
    }

    public function isUniqueConnectedClientId($value)
    {
        $connectedStore = [];
        $collection = $this->getCollection()
            ->addFieldToFilter('path', Signature::PATH_XERO_OAUTH_V2_IS_CONNECTED)
            ->addFieldToFilter('value', 1);

        foreach ($collection as $config) {
            $connectedStore[]  = "scope = '{$config->getScope()}' and scope_id = {$config->getScopeId()}";
        }

        if (empty($connectedStore)) {
            return true;
        }
        $resultCondition = '(' . implode(') ' . Select::SQL_OR . ' (', $connectedStore) . ')';

        $collection = $this->getCollection()
            ->addFieldToFilter('value', $value);
        $collection->getSelect()->where($resultCondition, null, Select::TYPE_CONDITION);

        return !$collection->getSize();
    }
}
