<?php
namespace Magenest\Xero\Model;

use Magenest\Xero\Helper\Signature;
use Magenest\Xero\Model\Config\Source\PrefixPosition;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection as CreditCollection;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Directory\Model\CountryFactory;

class Helper
{
    protected $_scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

    protected $_scopeId = 0;

    protected $_savedIds = [];

    protected $_xmlLog;

    protected $_coreConfig;

    protected $_messageManager;

    protected $_configWriter;

    protected $_countryFactory;

    protected $_productFactory;

    protected $_address = [];

    protected $_parser;
    /**
     * @var Registry
     */
    protected $_registry;

    public function __construct(
        XmlLogFactory $xmlLogFactory,
        CoreConfig $coreConfig,
        ManagerInterface $manager,
        WriterInterface $writer,
        CountryFactory $countryFactory,
        ProductFactory $productFactory,
        Parser $parser,
        Registry $registry
    ) {
        $this->_xmlLog = $xmlLogFactory;
        $this->_coreConfig = $coreConfig;
        $this->_messageManager = $manager;
        $this->_configWriter = $writer;
        $this->_countryFactory = $countryFactory;
        $this->_productFactory = $productFactory;
        $this->_parser = $parser;
        $this->_registry = $registry;
    }

    public function setScope($scope)
    {
        $this->_scope = $scope;
    }

    public function getScope()
    {
        return $this->_scope;
    }

    public function setScopeId($scopeId)
    {
        $this->_scopeId = $scopeId;
    }

    public function getScopeId()
    {
        return $this->_scopeId;
    }

    public function addSavedId($key, $id, $type)
    {
            $this->_savedIds[$type][$this->_scope][$this->_scopeId][$key] = $id;
    }

    public function getSavedId($key, $type)
    {
        if ($type == "BankTransaction") {
            $key = "NONE";
        }
        return isset($this->_savedIds[$type][$this->_scope][$this->_scopeId][$key]) ?
                    $this->_savedIds[$type][$this->_scope][$this->_scopeId][$key] : null;
    }

    public function getIdInCollectionByMagentoId($id, $type)
    {
        $collection = $this->_xmlLog->create()->getCollection()
            ->addFieldToFilter('magento_id', $id)
            ->addFieldToFilter('type', $type)
            ->setOrder('id', 'DESC');
        return $collection->getFirstItem()->getId();
    }

    public function isXeroConnected($id)
    {
        if (!$this->_coreConfig->getConfigValueByScope(
            Config::XML_PATH_XERO_IS_CONNECTED,
            ScopeInterface::SCOPE_WEBSITES,
            $id
        )) {
            return $this->handleConnectionError();
        }
        return true;
    }

    protected function handleConnectionError()
    {
        $this->_messageManager->addErrorMessage('Please connect the integration to your Xero account first!');
        return false;
    }

    /**
     * @param $id
     * @param $factory
     * @param $idKey
     * @return boolean
     */
    public function isXeroConnectedByIds($id, $factory, $idKey)
    {
        if (!$this->isMultipleWebsiteEnable()) {
            if (!$this->isDefaultXeroAccountConnected()) {
                return $this->handleConnectionError();
            }
            return true;
        }

        if (!is_array($id)) {
            $id = [$id];
        }
        $collection = $factory;
        if (method_exists($factory, 'create')){
            $collection = $factory->create();
        }
        $collection
            ->addAttributeToSelect('*')
            ->addFieldToFilter($idKey, ['IN' => $id]);
        $websiteIds = [];
        if ($collection instanceof ProductCollection) {
            $websiteIds = $this->getProductWebsiteIds($collection);
        } elseif ($collection instanceof CustomerCollection) {
            $websiteIds = $this->getCustomerWebsiteIds($collection);
        } elseif ($collection instanceof OrderCollection
            || $collection instanceof InvoiceCollection
            || $collection instanceof CreditCollection
        ) {
            $websiteIds = $this->getOrderWebsiteIds($collection);
        }

        $websiteIds = array_unique($websiteIds);
        foreach ($websiteIds as $id) {
            if (!$this->isXeroConnected($id)) {
                return false;
            }
        }
        return true;
    }

    protected function getProductWebsiteIds($collection)
    {
        $websiteIds = [];
        foreach ($collection as $model) {
            $websiteIds = array_merge_recursive($model->getWebsiteIds(), $websiteIds);
        }
        return $websiteIds;
    }

    protected function getCustomerWebsiteIds($collection)
    {
        $websiteIds = [];
        foreach ($collection as $model) {
            $websiteIds[] = $model->getWebsiteId();
        }
        return $websiteIds;
    }

    protected function getOrderWebsiteIds($collection)
    {
        $websiteIds = [];
        foreach ($collection as $model) {
            $websiteIds[] = $model->getStore()->getWebsiteId();
        }
        return $websiteIds;
    }

    public function isMultipleWebsiteEnable()
    {
        return $this->_coreConfig->getConfigValueByScope(
            Config::XML_PATH_XERO_MULTIPLE_ENABLED,
            'default',
            0
        );
    }

    protected function isDefaultXeroAccountConnected()
    {
        return $this->_coreConfig->getConfigValueByScope(
            Config::XML_PATH_XERO_IS_CONNECTED,
            'default',
            0
        );
    }

    public function getConfigDefault($path)
    {
        return $this->_coreConfig->getConfigValueByScope(
            $path,
            'default',
            0
        );
    }

    public function getConfig($path)
    {
        return $this->_coreConfig->getConfigValueByScope(
            $path,
            $this->_scope,
            $this->_scopeId
        );
    }

    public function isUniqueConnectedClientId($value)
    {
        return $this->_coreConfig->isUniqueConnectedClientId($value);
    }

    public function getDiscount($item)
    {
        if ($item->getDiscountAmount() > 0) {
            return $item->getDiscountAmount();
        } elseif ($item->getDiscountPercent() > 0) {
            return $discountAmount = $item->getDiscountPercent() * $item->getRowTotalInclTax() / 100;
        }
        return 0.0;
    }

    public function getDiscountPercent($item)
    {
        if ($item->getDiscountPercent() > 0) {
            return $item->getDiscountPercent();
        } elseif ($item->getDiscountAmount() > 0) {
            return $discountAmount = $item->getDiscountAmount() / $item->getRowTotalInclTax() * 100;
        }
        return 0.0;
    }

    public function saveConfig($path, $value)
    {
        $this->_configWriter->save($path, $value, $this->_scope, $this->_scopeId);
    }

    public function getCountryName($countryCode)
    {
        $country = $this->_countryFactory->create()->load($countryCode);
        return $country->getName();
    }

    public function createProduct($item)
    {
        $product = $this->_productFactory->create();
        $product->setData([
            'entity_id' => $item->getItemId(),
            'price' => $item->getPrice(),
            'sku' => $item->getSku(),
            'type_id' => $item->getProductType(),
            'name' => preg_replace('/[\x00-\x1F\x7F]/u', '', $item->getName()),
            'cost' => $item->getCost()
        ]);
        return $product;
    }

    public function setDefaultBillingAddress($billing)
    {
        $this->_address['billing'] = $billing;
    }
    public function setDefaultShippingAddress($shipping)
    {
        $this->_address['shipping'] = $shipping;
    }
    public function getDefaultBillingAddress()
    {
        return isset($this->_address['billing']) ? $this->_address['billing'] : false;
    }
    public function getDefaultShippingAddress()
    {
        return isset($this->_address['shipping']) ? $this->_address['shipping'] : false;
    }

    public function getAuthorization()
    {
        return base64_encode(
            $this->getConfig(Signature::PATH_CLIENT_ID).":".
            $this->getConfig(Signature::PATH_CLIENT_SECRET)
        );
    }

    public function getRefreshCode()
    {
        if ($this->isMultipleWebsiteEnable()) {
            return $this->getConfig(Signature::PATH_REFRESH_TOKEN);
        }
        return $this->getConfigDefault(Signature::PATH_REFRESH_TOKEN);
    }

    public function generateCacheIdByScope($identifier)
    {
        if ($this->isMultipleWebsiteEnable()) {
            return $this->getScope()."-".$this->getScopeId()."-".$identifier;
        }
        return 'default-0-'.$identifier;
    }

    public function parseXml($xml)
    {
        return $this->_parser->parseXML($xml);
    }

    /**
     * @param $incrementId
     * @return string
     */
    public function getPrefixId($incrementId){
        if ($this->getConfig('magenest_xero_config/xero_order/prefix_enabled')){
            $prefix = $this->getConfig('magenest_xero_config/xero_order/prefix');
            if ($this->getConfig('magenest_xero_config/xero_order/prefix_position')== PrefixPosition::PREFIX){
                $incrementId = $prefix.$incrementId;
            }else
            {
                $incrementId .=  $prefix;
            }

        }
        return $incrementId;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setRegistry($key, $value)
    {
        $this->_registry->register($key, $value);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getRegistry($key)
    {
        return $this->_registry->registry($key);
    }

    /**
     * @param $key
     */
    public function unsetRegistry($key)
    {
        return $this->_registry->unregister($key);
    }
}
