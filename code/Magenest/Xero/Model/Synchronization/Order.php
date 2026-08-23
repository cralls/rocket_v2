<?php
namespace Magenest\Xero\Model\Synchronization;

use Magenest\Xero\Model\Log\Status;
use Magenest\Xero\Model\LogFactory;
use Magenest\Xero\Model\Synchronization;
use Magenest\Xero\Model\XeroClient;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\FileSystemException;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use Magenest\Xero\Model\RequestLogFactory;
use Magenest\Xero\Model\QueueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item as TaxItem;
use Magenest\Xero\Model\TaxMappingFactory;
use Magenest\Xero\Model\Helper;
use Magenest\Xero\Model\XmlLogFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magenest\Xero\Model\Synchronization\BankTransaction;
use Magento\Bundle\Model\Product\Price;

/**
 * Class Order
 * @package Magenest\Xero\Model\Synchronization
 */
class Order extends Synchronization
{
    /**
     * Purchase Order status
     */
    const STATUS_DELETED = 'DELETED';

    const STATUS_AUTHORISED = 'AUTHORISED';

    const STATUS_SUBMITTED = 'SUBMITTED';

    const STATUS_BILLED = 'BILLED';

    const STATUS_DRAFT = 'DRAFT';

    const STATUS_VOIDED = 'VOIDED';

    /**
     * @var string
     */
    protected $type = 'OrderToInvoice';

    protected $syncType = 'Invoice';

    protected $syncIdKey = 'InvoiceNumber';

    protected $syncTypeKey = 'Invoice';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var array
     */
    protected $orders = [];

    /**
     * @var array
     */
    protected $invoices = [];

    /**
     * @var Customer
     */
    protected $syncCustomer;

    /**
     * @var array
     */
    protected $contacts = [];

    /**
     * @var string
     */
    protected $saleAccountId;

    protected $shippingAccountId;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    protected $customer = [];

    protected $existedInvoice = [];

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    protected $customerXmlToSync = "";

    protected $scopeConfig;

    /**
     * @var TaxItem
     */
    protected $taxItems;

    protected $taxMappingFactory;

    protected $orderToInvoiceFlag;

    protected $_xmlLogFactory;

    protected $_customerFactory;

    protected $_listProduct = [];

    protected $syncProduct;

    protected $_productXml = [];

    protected $_customerXml = [];

    protected $_taxType;

    protected $_directoryList;

    protected $_file;

    protected $_productRepository;

    protected $_syncBankTransaction;
    /**
     * Mapping Order States with Purchase Order Status
     *
     * @var array
     */
    protected $status = [
        SalesOrder::STATE_NEW => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PENDING_PAYMENT => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PAYMENT_REVIEW => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PROCESSING => self::STATUS_AUTHORISED,
        SalesOrder::STATE_COMPLETE => self::STATUS_BILLED,
        SalesOrder::STATE_CANCELED => self::STATUS_DELETED,
        SalesOrder::STATE_CLOSED => self::STATUS_DELETED,
        SalesOrder::STATE_HOLDED => self::STATUS_SUBMITTED,
    ];

    /**
     * Mapping Order States with Purchase Order Status
     *
     * @var array
     */
    protected $invoiceStatus = [
        SalesOrder::STATE_NEW => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PENDING_PAYMENT => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PAYMENT_REVIEW => self::STATUS_SUBMITTED,
        SalesOrder::STATE_PROCESSING => self::STATUS_AUTHORISED,
        SalesOrder::STATE_COMPLETE => self::STATUS_BILLED,
        SalesOrder::STATE_CANCELED => self::STATUS_DELETED,
        SalesOrder::STATE_CLOSED => self::STATUS_DELETED,
        SalesOrder::STATE_HOLDED => self::STATUS_SUBMITTED,
    ];

    /**
     * Order constructor.
     * @param XeroClient $xeroClient
     * @param LogFactory $logFactory
     * @param Customer $syncCustomer
     * @param OrderFactory $orderFactory
     * @param RequestLogFactory $requestLogFactory
     * @param ScopeConfigInterface $configInterface
     * @param Payment $payment
     * @param Account $accountConfig
     * @param QueueFactory $queueFactory
     * @param CollectionFactory $collectionFactory
     * @param ProductFactory $productFactory
     * @param TaxItem $item
     * @param TaxMappingFactory $taxMappingFactory
     * @param Helper $helper
     * @param XmlLogFactory $xmlLogFactory
     * @param CustomerFactory $customerFactory
     * @param Item $syncProduct
     * @param DirectoryList $directoryList
     * @param File $file
     * @param ProductRepositoryInterface $productRepository
     * @param BankTransaction $syncBankTransaction
     * @throws \Exception
     */
    public function __construct(
        XeroClient $xeroClient,
        LogFactory $logFactory,
        Synchronization\Customer $syncCustomer,
        OrderFactory $orderFactory,
        RequestLogFactory $requestLogFactory,
        ScopeConfigInterface $configInterface,
        Payment $payment,
        Account $accountConfig,
        QueueFactory $queueFactory,
        CollectionFactory $collectionFactory,
        ProductFactory $productFactory,
        TaxItem $item,
        TaxMappingFactory $taxMappingFactory,
        Helper $helper,
        XmlLogFactory $xmlLogFactory,
        CustomerFactory $customerFactory,
        Synchronization\Item $syncProduct,
        DirectoryList $directoryList,
        File $file,
        ProductRepositoryInterface $productRepository,
        BankTransaction $syncBankTransaction
    )
    {
        $this->syncCustomer = $syncCustomer;
        $this->orderFactory = $orderFactory;
        $this->payment = $payment;
        $this->orderToInvoiceFlag = $configInterface->getValue('magenest_xero_config/xero_order/order_invoice_enabled');
        $this->saleAccountId = $accountConfig->getAccountId($accountConfig::SALE_ACC_TYPE);
        $this->shippingAccountId = $accountConfig->getAccountId($accountConfig::SHIPPING_ACC_TYPE);
        $this->limit = 1000;
        $this->collectionFactory = $collectionFactory;
        $this->id = "increment_id";
        $this->taxItems = $item;
        $this->productFactory = $productFactory;
        $this->scopeConfig = $configInterface;
        $this->taxMappingFactory = $taxMappingFactory;
        $this->_xmlLogFactory = $xmlLogFactory;
        $this->_customerFactory = $customerFactory;
        $this->syncProduct = $syncProduct;
        $this->_taxType = $this->scopeConfig->getValue('magenest_xero_config/xero_order/tax_type') ? 'Exclusive' : 'Inclusive';
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        $this->_productRepository = $productRepository;
        $this->_syncBankTransaction = $syncBankTransaction;
        parent::__construct($xeroClient, $logFactory, $requestLogFactory, $queueFactory, $helper);
    }

    /**
     * @param $status
     * @return mixed
     * @throws LocalizedException
     */
    public function convertToInvoiceStatus($status)
    {
        if (isset($this->invoiceStatus[$status])) {
            return $this->invoiceStatus[$status];
        } else {
            throw new LocalizedException(__('Doesn\'t exist order state!'));
        }
    }

    /**
     * @param $status
     * @return mixed
     * @throws LocalizedException
     */
    public function convertToPurchaseOrderStatus($status)
    {
        if (isset($this->status[$status])) {
            return $this->status[$status];
        } else {
            throw new LocalizedException(__('Doesn\'t exist order state!'));
        }
    }

    /**
     * return an xml string of orders
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @throws
     */
    public function addRecord($order)
    {
        $incrementId = $order->getIncrementId();

        $state = $order->getState();
        $status = $this->convertToInvoiceStatus($state);
        if ($state === SalesOrder::STATE_HOLDED) {
            return '';
        }
        if ($order->getTotalDue() == 0) {
            $status = "BILLED";
        }
        if ($this->helper->isMultipleWebsiteEnable()) {
            $invoiceExisted = $this->invoiceExistedMultipleWebsite($incrementId, $order->getStore()->getWebsiteId());
        } else {
            $invoiceExisted = $this->invoiceExisted($incrementId);
        }
        /** if order is completed but there is no invoice created on Xero yet */
        if ($status == self::STATUS_BILLED && !$invoiceExisted) {
            $this->addPayment($order);
        } elseif ($status == self::STATUS_BILLED && $invoiceExisted && $invoiceExisted['Status'] == 'PAID') {
            $log = $this->logFactory->create();
            $log->addData([
                'type' => 'OrderToInvoice',
                'entity_id' => $incrementId,
                'dequeue_time' => time(),
                'status' => Status::SUCCESS_STATUS,
                'xero_id' => $invoiceExisted['InvoiceID'],
                'msg' => 'Order is already paid/closed, a record update process was not performed.',
                'xml_log_id' => $this->helper->getIdInCollectionByMagentoId($incrementId, $this->type)
            ])->save();
            return '';
        } elseif ($status == self::STATUS_BILLED && $invoiceExisted && $invoiceExisted['Status'] != self::STATUS_AUTHORISED) {
            $this->addPayment($order);
        } elseif ($status == self::STATUS_BILLED && $invoiceExisted) {
            $this->addPayment($order);
            return 'payment';
        } elseif ($status == self::STATUS_DELETED && !$invoiceExisted) {
            $log = $this->logFactory->create();
            $log->addData([
                'type' => 'OrderToInvoice',
                'entity_id' => $incrementId,
                'dequeue_time' => time(),
                'status' => Status::SUCCESS_STATUS,
                'xero_id' => 'NONE',
                'msg' => 'Order is already paid/closed, a record update process was not performed.',
                'xml_log_id' => $this->helper->getIdInCollectionByMagentoId($incrementId, $this->type)
            ])->save();
            return '';
        }

        if (isset($invoiceExisted['Status']) && $invoiceExisted['Status'] === 'PAID' && $status === self::STATUS_DELETED) {
            $log = $this->logFactory->create();
            $log->addData([
                'type' => 'OrderToInvoice',
                'entity_id' => $incrementId,
                'dequeue_time' => time(),
                'status' => Status::SUCCESS_STATUS,
                'xero_id' => $invoiceExisted['InvoiceID'],
                'msg' => 'Order is already paid/closed, a record update process was not performed.',
                'xml_log_id' => $this->helper->getIdInCollectionByMagentoId($incrementId, $this->type)
            ])->save();
            return '';
        }

        return $this->addInvoiceRecord($order, $invoiceExisted);
    }

    protected function addInvoiceRecord($order, $invoiceExisted)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $status = $this->convertToInvoiceStatus($order->getState());
        if ($order->getTotalDue() == 0) {
            $status = "BILLED";
        }
        if ($status == self::STATUS_DELETED && $invoiceExisted['Status'] == self::STATUS_AUTHORISED) {
            $status = 'VOIDED';
        }
        if ($status==self::STATUS_BILLED && (!$invoiceExisted || $invoiceExisted['Status'] != self::STATUS_AUTHORISED)) {
            $status=self::STATUS_AUTHORISED;
        }
        $createdAt = date('Y-m-d', strtotime($order->getCreatedAt()));
        $datetime = new \DateTime($order->getUpdatedAt());
        $datetime->modify('+30 day');
        $updatedAt = $datetime->format('Y-m-d');
        $xml = '<Invoice>';
        $xml .= '<InvoiceNumber>' . $this->helper->getPrefixId($order->getIncrementId()) . '</InvoiceNumber>';
//        $xml .= $this->syncCustomer->getContactXml($orderCollection);
        $xml .= $this->getCustomerXml($order);
        $xml .= '<Type>ACCREC</Type>';
        $xml .= '<CurrencyCode>' . $order->getOrderCurrencyCode() . '</CurrencyCode>';
        $xml .= '<Date>' . $createdAt . '</Date>';
        $xml .= '<DueDate>' . $updatedAt . '</DueDate>';
        $xml .= '<Status>'.$status.'</Status>';
        $xml .= '<LineAmountTypes>'.$this->_taxType.'</LineAmountTypes>';
        $xml .= $this->getItemsOrderXml($order);
        $xml .= '</Invoice>';

        return $xml;
    }
    protected function setContactList($order)
    {
        $websiteId = 0;
        if ($this->helper->isMultipleWebsiteEnable()) {
            $websiteId = $order->getStore()->getWebsiteId();
        }
        $contacts = $this->getContactsOnXero();
        foreach ($contacts as $key => $contact) {
            if (!is_numeric($key)) {
                $contact = $contacts;
                if (isset($contact['ContactNumber']) && isset($contact['EmailAddress'])) {
                    $this->customer[$websiteId][$contact['EmailAddress']] = $contact['ContactNumber'];
                }
                break;
            }
            if (isset($contact['ContactNumber']) && isset($contact['EmailAddress'])) {
                $this->customer[$websiteId][$contact['EmailAddress']] = $contact['ContactNumber'];
            }
        }
    }
    /**
     * @param $order
     * @throws \Exception
     */
    public function guestToXml($order)
    {
        $email = $order->getCustomerEmail();
        $websiteId = 0;
        if ($this->helper->isMultipleWebsiteEnable()) {
            $websiteId = $order->getStore()->getWebsiteId();
        }
        if (isset($this->customer[$websiteId][$email])) {
            return;
        }
        if (!isset($this->customer[$websiteId])) {
            if ($this->helper->isMultipleWebsiteEnable()) {
                $this->setWebsiteScopeConfig($websiteId);
            }
            $this->setContactList($order);
        }

        if (!isset($this->customer[$websiteId][$email])) {
            if (!$order->getCustomerId()) {
                $address = $order->getShippingAddress();
                if (!$address) {
                    $address = $order->getBillingAddress();
                }
                $code = substr(hash('sha256', $order->getCustomerEmail()), 0, 5);
                $customerXml = '<Contact>';
                $customerXml .= '<ContactNumber>' . $code . '</ContactNumber>';
                $customerXml .= '<Name>' . $address->getFirstname() . ' ' . $address->getLastname() . ' ' . $code . '</Name>';
                $customerXml .= '<FirstName>' . $address->getFirstname() . '</FirstName>';
                $customerXml .= '<LastName>' . $address->getLastname() . '</LastName>';
                $customerXml .= '<EmailAddress>' . $order->getCustomerEmail() . '</EmailAddress>';
                $customerXml .= $this->getAddressXml($order);
                $customerXml .= '</Contact>';
                if (!isset($this->customerXmlToSync[$websiteId])) {
                    if (!is_array($this->customerXmlToSync)) {
                        $this->customerXmlToSync = [];
                    }
                    $this->customerXmlToSync[$websiteId] = '';
                }
                $this->customerXmlToSync[$websiteId] .= $customerXml;
                $this->customer[$websiteId][$email] = $code;
                $this->_xmlLogFactory->create()->setData([
                    'magento_id' => $code,
                    'xml_log' => $customerXml,
                    'type' => 'Contact',
                    'scope' => $this->helper->getScope(),
                    'scope_id' => $this->helper->getScopeId()
                ])->save();
            } else {
                $this->setCustomerXml($order);
            }
        }
    }

    public function syncAllGuestToXero()
    {
        if (is_array($this->customerXmlToSync)) {
            foreach ($this->customerXmlToSync as $id => $xml) {
                if (empty($xml)) {
                    continue;
                }
                $this->setWebsiteScopeConfig($id);
                $xml = '<Contacts>'.$xml.'</Contacts>';
                $this->syncCustomer->syncData($xml);
            }
            $this->customerXmlToSync = [];
        } else {
            if (empty($this->customerXmlToSync)) {
                return;
            }
            $this->customerXmlToSync = '<Contacts>'.$this->customerXmlToSync.'</Contacts>';
            $this->syncCustomer->syncData($this->customerXmlToSync);
            $this->customerXmlToSync = "";
        }
    }

    public function setCustomerXmlToSync($xml)
    {
        $this->customerXmlToSync = $xml;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @throws \Exception
     */
    public function getCustomerXml($order)
    {
        $websiteId = 0;
        if ($this->helper->isMultipleWebsiteEnable()) {
            $websiteId = $order->getStore()->getWebsiteId();
        }

        if (!isset($this->customer[$websiteId][$order->getCustomerEmail()])) {
            $this->guestToXml($order);
        }

        $xml = '<Contact><ContactNumber>'.$this->customer[$websiteId][$order->getCustomerEmail()].'</ContactNumber>';
        $xml .= $this->getAddressXml($order);
        $xml .= '</Contact>';

        return $xml;
    }

    /**
     * @param $order
     */
    protected function addPayment($order)
    {
        $this->payment->addRecord($order);
    }

    /**
     * @param SalesOrder $order
     * @return string
     */
    public function getItemsOrderXml($order)
    {
        $this->getProductsOnXero();
        $xml = '<LineItems>';
        $taxes = $this->taxItems->getTaxItemsByOrderId($order->getId());
        $taxByItemId = [];
        foreach ($taxes as $tax) {
            if ($tax['item_id']) {
                $taxByItemId[$tax['item_id']] = $tax;
            }
        }
        $total = 0;
        $subtotal = 0;
        $lineTotal = 0;
        $items = $order->getItems();
        /** @var \Magento\Sales\Model\Order\Item $item */

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $this->getProduct($item);
            $productType = $product->getData('price_type');
            if ($item->getProductType() == "configurable" || ( $item->getProductType()  == "bundle" && $productType == Price::PRICE_TYPE_DYNAMIC ) ) {
                continue;
            }
            if ($this->scopeConfig->getValue('magenest_xero_config/xero_init_data/enabled')) {
                if (!isset($this->_listProduct[$this->helper->getScopeId()][$item->getSku()])) {
                    $this->setProductXml($product);
                }
            }
            $id = $item->getItemId();
            $parent = $item->getParentItem();
            $price = $this->getItemPrice($item);
            $taxAmount = $item->getTaxAmount() ? $item->getTaxAmount() : 0;
            $discountPercent = $this->helper->getDiscount($item);
            if ($parent && $parent->getProductType() == "configurable") {
                $id = $parent->getItemId();
                $price = $this->getItemPrice($parent);
                $taxAmount = $parent->getTaxAmount() > 0 ? $parent->getTaxAmount() : 0.0;
                $discountPercent = $this->helper->getDiscount($parent);
            }

            $sku = $item->getSku();
            $trackingCategory = null;
            $categoryName = '';
            $optionName = '';
            $saleAccountId = $this->saleAccountId;
            if ($product && $product->getTypeId() != "configurable") {
                $sku = $product->getSku();

                if ($this->helper->isMultipleWebsiteEnable()) {
                    $saleAccountId = $this->getAttributeByWebsite($item, 'sale_id');
                    $trackingCategory = $this->getAttributeByWebsite($item, 'tracking_category');
                } else {
                    $trackingCategory = $product->getTrackingCategory();
                    $saleAccountId = empty($product->getData('sale_id')) ? $saleAccountId : $product->getData('sale_id');
                }
                $saleAccountId = $saleAccountId ? : $this->saleAccountId;

                if ($trackingCategory) {
                    $array = explode('/', $trackingCategory);
                    if (count($array) == 2) {
                        list($categoryName, $optionName) = $array;
                    }
                }
            }
            $name = preg_replace('/[^A-Za-z0-9\s\-]/', '', $item->getName());
            if (strlen($name) >= 50) {
                $name = substr($name, 0, 48);
            }
            $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);
            $xml .= '<LineItem>';
            $xml .= '<Description>' . $name  . '</Description>';
            $xml .= '<ItemCode>' . $sku . '</ItemCode>';
            $xml .= '<Quantity>' . $item->getQtyOrdered() . '</Quantity>';
            $xml .= '<UnitAmount>' . $price . '</UnitAmount>';
            $xml .= '<TaxAmount>'. $taxAmount .'</TaxAmount>';

            if (!$taxAmount) {
                $xml .= '<TaxType>NONE</TaxType>';
            } else {
                $xml .= $this->retrievedTaxTypeMapping($item, $taxByItemId);
            }

            $xml .= '<AccountCode>' . $saleAccountId . '</AccountCode>';
            if ($trackingCategory) {
                $xml .= '<Tracking>';
                $xml .= '<TrackingCategory>';
                $xml .= '<Name>'.$categoryName.'</Name>';
                $xml .= '<Option>'.$optionName.'</Option>';
                $xml .= '</TrackingCategory>';
                $xml .= '</Tracking>';
            }
            if ($discountPercent > 0) {
                $xml .= '<DiscountAmount>' . $discountPercent . '</DiscountAmount>';
            }
            $xml .= '</LineItem>';
            if ($this->getTaxType() == "Inclusive") {
                $total += $price*$item->getQtyOrdered() - $item->getDiscountAmount();
            } else {
                $total += $price*$item->getQtyOrdered() + $taxAmount - $item->getDiscountAmount();
            }
            $subtotal += $total;
            $lineTotal += round($total,2);
        }
        $amountDiff = round($subtotal,2) - $lineTotal;
        if(abs(round($amountDiff,2)) > 0 )
        {
            $xml .= $this->getAmountHandlerXml($amountDiff);
        }
        $xml .= $this->getShippingXml($order);
        $xml .= '</LineItems>';
        return $xml;
    }

    public function getProductsOnXero()
    {
        $websiteId = $this->helper->getScopeId();
        if (!isset($this->_listProduct[$websiteId])) {
            $helper = $this->xeroClient->getSignature();
            $helper->setUri('Items');
            $url = $helper->getUri();
            $response = $this->xeroClient->sendRequest($url);

            $parsedResponse = $this->parseXML($response);
            if (isset($parsedResponse['Items']['Item'])) {
                if (!isset($parsedResponse['Items']['Item'][0])) {
                    $tmp = $parsedResponse['Items']['Item'];
                    $parsedResponse['Items']['Item'] = [];
                    $parsedResponse['Items']['Item'][] = $tmp;
                }
                $list = [];
                foreach ($parsedResponse['Items']['Item'] as $item) {
                    $list[$websiteId][$item['Code']] = $item;
                }
                $this->_listProduct = array_merge_recursive($this->_listProduct, $list);
                return $this->_listProduct;
            }
        }
        return $this->_listProduct;
    }

    protected function getAmountHandlerXml($amountDiff)
    {
        $xml = '<LineItem>';
        $xml .= '<Description>Miss Match Amount Handler</Description>';
        $xml .= '<ItemCode>amount_handler</ItemCode>';
        $xml .= '<Quantity>1</Quantity>';
        $xml .= '<UnitAmount>' . $amountDiff . '</UnitAmount>';
        $xml .= '<TaxAmount>0</TaxAmount>';
        $xml .= '<TaxType>NONE</TaxType>';
        $xml .= '<AccountCode>' . $this->saleAccountId . '</AccountCode>';
        $xml .= '</LineItem>';

        return $xml;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getTaxXml($order)
    {
        $taxAmount = $order->getTaxAmount() > 0 ? $order->getTaxAmount() : 0.0;
        $xml = '<LineItem>';
        $xml .= '<Description>Order Tax</Description>';
        $xml .= '<ItemCode>tax</ItemCode>';
        $xml .= '<Quantity>1</Quantity>';
        $xml .= '<UnitAmount>' . $taxAmount . '</UnitAmount>';
        $xml .= '<TaxType>NONE</TaxType>';
        $xml .= '<TaxAmount>0</TaxAmount>';
        $xml .= '<AccountCode>' . $this->saleAccountId . '</AccountCode>';
        $xml .= '</LineItem>';

        return $xml;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getShippingXml($order)
    {
        if ($order->getIsVirtual()) {
            return "";
        }
        if ($this->_taxType == "Inclusive") {
            $shippingAmount = $order->getShippingInclTax();
        } else {
            $shippingAmount = $order->getShippingAmount();
        }
        $shippingAmount = $shippingAmount ? : 0.0;
        $amount = $order->getShippingTaxAmount() > 0 ? $order->getShippingTaxAmount() : 0.0;
        $shippingAccountId = "";
        if ($this->helper->isMultipleWebsiteEnable()) {
            $shippingAccountId = $this->helper->getConfig('magenest_xero_config/xero_account/shipping_id');
        }
        $shippingAccountId = $shippingAccountId ? : $this->shippingAccountId;

        $xml = '<LineItem>';
        $xml .= '<Description>Order Shipping Cost</Description>';
        $xml .= '<ItemCode>shipping</ItemCode>';
        $xml .= '<Quantity>1</Quantity>';
        $xml .= '<UnitAmount>' . $shippingAmount . '</UnitAmount>';
        $xml .= '<TaxAmount>'.$amount.'</TaxAmount>';
        if (!$amount) {
            $xml .= '<TaxType>NONE</TaxType>';
        }
        $xml .= '<AccountCode>' . $shippingAccountId . '</AccountCode>';
        $xml .= '</LineItem>';

        return $xml;
    }

    /**
     * Check if order existed on Xero
     *
     * @param $incrementId
     * @return mixed
     */
    protected function orderExisted($incrementId)
    {
        if (!$this->orders) {
            $this->orders = $this->getOrdersOnXero();
        }
        foreach ($this->orders as $key => $order) {
            if (isset($order['PurchaseOrderNumber']) && $order['PurchaseOrderNumber']==$incrementId) {
                unset($this->orders[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     * @throws
     */
    protected function getOrdersOnXero()
    {
        $helper = $this->xeroClient->getSignature();
        $helper->setUri('PurchaseOrders');
        $url = $helper->getUri();
        $response = $this->xeroClient->sendRequest($url);
        $parsedResponse = $this->parseXML($response);
        if (isset($parsedResponse['PurchaseOrders']['PurchaseOrder'])) {
            return $parsedResponse['PurchaseOrders']['PurchaseOrder'];
        }

        return [];
    }

    /**
     * Check if order existed on Xero
     *
     * @param $incrementId
     * @return mixed
     */
    public function invoiceExisted($incrementId)
    {
        if (!$this->invoices) {
            $this->invoices = $this->getInvoicesOnXero();
            if (!isset($this->invoices[0])) {
                $tmp = $this->invoices;
                $this->invoices = [];
                $this->invoices[] = $tmp;
            }
            foreach ($this->invoices as $key => $invoice) {
                if (isset($invoice['InvoiceNumber'])) {
                    $this->existedInvoice[$invoice['InvoiceNumber']] = $invoice;
                }
            }
        }
        if (isset($this->existedInvoice[$incrementId])) {
            return $this->existedInvoice[$incrementId];
        }
        return false;
    }

    public function invoiceExistedMultipleWebsite($incrementId, $id)
    {
        if (!isset($this->invoices[$id])) {
            $this->invoices[$id] = $this->getInvoicesOnXero();
            foreach ($this->invoices[$id] as $key => $invoice) {
                if (isset($invoice['InvoiceNumber'])) {
                    $this->existedInvoice[$id][$invoice['InvoiceNumber']] = $invoice;
                }
            }
        }
        if (isset($this->existedInvoice[$id][$incrementId])) {
            return $this->existedInvoice[$id][$incrementId];
        }
        return false;
    }

    /**
     * @return array
     * @throws
     */
    protected function getInvoicesOnXero()
    {
        $helper = $this->xeroClient->getSignature();
        $helper->setUri('Invoices');
        $url = $helper->getUri();
        $response = $this->xeroClient->sendRequest($url);
        $parsedResponse = $this->parseXML($response);
        if (isset($parsedResponse['Invoices']['Invoice'])) {
            return $parsedResponse['Invoices']['Invoice'];
        }

        return [];
    }

    /**
     * @return array
     * @throws
     */
//    protected function getContactsOnXero()
//    {
//        $helper = $this->xeroClient->getSignature();
//        $helper->setUri('Contacts');
//        $url = $helper->getUri();
//        $response = $this->xeroClient->sendRequest($url);
//        $parsedResponse = $this->parseXML($response);
//        if (isset($parsedResponse['Contacts']['Contact'])) {
//            return $parsedResponse['Contacts']['Contact'];
//        }
//
//        return [];
//    }
    protected function getContactsOnXero()
    {
        $varPath = $this->_directoryList->getPath('var');
        $path = $varPath . '/xerocontact/contact.txt';
        try {
            return json_decode($this->_file->fileGetContents($path), true);
        } catch (FileSystemException $exception) {
        }
        return [];
    }

    /**
     * @param string $additionalXml
     */
    public function _additionalSync($additionalXml)
    {
        $this->syncPayments();
    }

    public function syncPayments()
    {
        $this->payment->syncPayments();
        $this->payment->unsetRecords();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->syncType;
    }

    /**
     * @param $item
     * @param $code
     * @return mixed
     */
    protected function getAttributeByWebsite($item, $code)
    {
        $product = $item->getProduct();
        return $product->getResource()->getAttributeRawValue($product->getId(), $code, $item->getStore());
    }

    public function getTaxType()
    {
        return $this->_taxType;
    }

    public function getAddressXml($order)
    {
        $xml = '<Addresses>';
        if ($billingAddress = $order->getBillingAddress()) {
            $billingPhone = $billingAddress->getTelephone();
            $xml .= '<Address>';
            $xml .= '<AddressType>POBOX</AddressType>';
            $xml .= '<AddressLine1>'.(implode(",", $billingAddress->getStreet()) ? : "N/A").'</AddressLine1>';
            $xml .= '<City>' . ($billingAddress->getCity() ? : "N/A") . '</City>';
            $xml .= '<Country>' . ($this->helper->getCountryName($billingAddress->getCountryId()) ? : "N/A") . '</Country>';
            $xml .= '<PostalCode>' . ($billingAddress->getPostcode() ? : "N/A") . '</PostalCode>';
            $xml .= '<Region>' . ($billingAddress->getRegion() ? : "N/A") . '</Region>';
            $xml .= '</Address>';
        }
        if ($shippingAddress = $order->getShippingAddress()) {
            $shippingPhone = $shippingAddress->getTelephone();
            $xml .= '<Address>';
            $xml .= '<AddressType>STREET</AddressType>';
            $xml .= '<AddressLine1>'.(implode(",", $shippingAddress->getStreet()) ? : "N/A").'</AddressLine1>';
            $xml .= '<City>' . ($shippingAddress->getCity() ? : "N/A") . '</City>';
            $xml .= '<Country>' . ($this->helper->getCountryName($shippingAddress->getCountryId()) ? : "N/A")  . '</Country>';
            $xml .= '<PostalCode>' . ($shippingAddress->getPostcode() ? : "N/A") . '</PostalCode>';
            $xml .= '<Region>' . ($shippingAddress->getRegion() ? : "N/A") . '</Region>';
            $xml .= '</Address>';
        }
        $xml .= '</Addresses>';
        if (isset($billingPhone) || isset($shippingPhone)) {
            $xml .= '<Phones>';
            if (isset($billingPhone)) {
                $xml .= '<Phone>';
                $xml .= '<PhoneType>DEFAULT</PhoneType>';
                $xml .= '<PhoneNumber>' . $billingPhone . '</PhoneNumber>';
                $xml .= '</Phone>';
            }
            if (isset($shippingPhone)) {
                $xml .= '<Phone>';
                $xml .= '<PhoneType>MOBILE</PhoneType>';
                $xml .= '<PhoneNumber>' . $shippingPhone . '</PhoneNumber>';
                $xml .= '</Phone>';
            }
            $xml .= '</Phones>';
        }
        return $xml;
    }

    public function getItemPrice($item)
    {
        if ($this->_taxType == "Inclusive") {
            return (float) $item->getPriceInclTax();
        } else {
            return (float) $item->getPrice();
        }
    }

    public function setCustomerXml($order)
    {
        $id = $order->getCustomerId();
        if ($this->scopeConfig->getValue('magenest_xero_config/xero_init_data/enabled')) {
            $customer = $this->_customerFactory->create()->load($id);
            if (!$customer->getDefaultBillingAddress()) {
                $this->helper->setDefaultBillingAddress($order->getBillingAddress());
            }
            if (!$customer->getDefaultShippingAddress()) {
                $this->helper->setDefaultShippingAddress($order->getShippingAddress());
            }
            $this->_customerXml[$customer->getEntityId()] = $this->syncCustomer->addRecord($customer);
        }
        $this->customer[$this->helper->getScopeId()][$order->getCustomerEmail()]= $id;
    }

    /**
     * @throws \Exception
     */
    public function syncMissingCustomer()
    {
        if ($this->_customerXml) {
            $this->_customerXml = "<Contacts>".implode("", $this->_customerXml)."</Contacts>";
            $this->syncCustomer->syncData($this->_customerXml);
            $this->_customerXml = [];
        }
    }

    public function setProductXml($product)
    {
        $this->_productXml[$product->getSku()] = $this->syncProduct->addRecord($product);
    }

    /**
     * @throws \Exception
     */
    public function syncMissingProduct()
    {
        if ($this->_productXml) {
            $data = $this->_productXml;
            $this->_productXml = "<Items>" . implode("", $this->_productXml) . "</Items>";
            $this->syncProduct->syncData($this->_productXml);
            $transactionXml = '';
            foreach ($data as $sku => $value) {
                $this->_listProduct[$this->helper->getScopeId()][$sku] = [$sku];
                $product = $this->_productRepository->get($sku);
                $transactionXml .= $this->_syncBankTransaction->addRecord($product);
            }
            if (!empty($transactionXml)) {
                $transactionXml = $this->_syncBankTransaction->addOtherTags($transactionXml);
                $this->_syncBankTransaction->syncData($transactionXml);
            }
            $this->_productXml = [];
        }
    }

    /**
     * @param $xml
     * @param string $method
     * @return string
     * @throws \Exception
     */
    public function syncData($xml, $method = 'POST')
    {
        $this->syncMissingCustomer();
        $this->syncMissingProduct();
        return parent::syncData($xml, $method);
    }
}
