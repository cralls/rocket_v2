<?php
namespace Magenest\Xero\Model\Synchronization;

use Magenest\Xero\Model\Helper;
use Magenest\Xero\Model\Log\Status;
use Magenest\Xero\Model\LogFactory;
use Magenest\Xero\Model\Synchronization;
use Magenest\Xero\Model\XeroClient;
use Magenest\Xero\Model\XmlLogFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magenest\Xero\Model\RequestLogFactory;
use Magenest\Xero\Model\QueueFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magenest\Xero\Model\TaxMappingFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item as OrderItem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;

class Invoice extends Synchronization
{
    /**
     * @var string
     */
    protected $type = 'InvoiceToInvoice';

    protected $collectionFactory;

    protected $syncType = 'Invoice';

    protected $syncIdKey = 'InvoiceNumber';

    protected $syncTypeKey = 'Invoice';

    /**
     * @var Synchronization\Payment
     */
    protected $syncPayment;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $invoiceFactory;

    /**
     * @var \Magenest\Xero\Model\TaxMappingFactory
     */
    protected $taxMappingFactory;

    /**
     * @var string
     */
    protected $saleAccountId;

    protected $shippingAccountId;

    /**
     * @var Order
     */
    protected $syncOrder;

    protected $customer = [];

    protected $customerXmlToSync = "";

    protected $orderToInvoiceFlag = false;

    protected $taxItem;

    protected $_xmlLogFactory;

    protected $_directoryList;

    protected $_file;

    /**
     * Invoice constructor.
     * @param XeroClient $xeroClient
     * @param LogFactory $logFactory
     * @param Account $accountConfig
     * @param Order $syncOrder
     * @param InvoiceFactory $invoiceFactory
     * @param RequestLogFactory $requestLogFactory
     * @param QueueFactory $queueFactory
     * @param Payment $payment
     * @param CollectionFactory $collectionFactory
     * @param TaxMappingFactory $taxMappingFactory
     * @param OrderItem $item
     * @param Helper $helper
     * @param XmlLogFactory $xmlLogFactory
     * @param DirectoryList $directoryList
     * @param File $file
     * @throws \Exception
     */
    public function __construct(
        XeroClient $xeroClient,
        LogFactory $logFactory,
        Account $accountConfig,
        Synchronization\Order $syncOrder,
        InvoiceFactory $invoiceFactory,
        RequestLogFactory $requestLogFactory,
        QueueFactory $queueFactory,
        Synchronization\Payment $payment,
        CollectionFactory $collectionFactory,
        TaxMappingFactory $taxMappingFactory,
        OrderItem $item,
        Helper $helper,
        XmlLogFactory $xmlLogFactory,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->syncOrder = $syncOrder;
        $this->saleAccountId = $accountConfig->getAccountId($accountConfig::SALE_ACC_TYPE);
        $this->shippingAccountId = $accountConfig->getAccountId($accountConfig::SHIPPING_ACC_TYPE);
        $this->invoiceFactory = $invoiceFactory;
        $this->syncPayment = $payment;
        $this->collectionFactory = $collectionFactory;
        $this->id = 'increment_id';
        $this->limit = 500;
        $this->taxMappingFactory = $taxMappingFactory;
        $this->taxItem = $item;
        $this->_xmlLogFactory = $xmlLogFactory;
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        parent::__construct($xeroClient, $logFactory, $requestLogFactory, $queueFactory, $helper);
    }

    /**
     * return an xml string of an invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return string
     * @throws
     */
    public function addRecord($invoice)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $updatedAt = date('Y-m-d', strtotime($invoice->getUpdatedAt()));
        $createdAt = date('Y-m-d', strtotime($invoice->getOrder()->getCreatedAt()));

        $order = $invoice->getOrder();
        $incrementId = $invoice->getIncrementId();

        if ($this->helper->isMultipleWebsiteEnable()) {
            $invoiceExisted = $this->syncOrder
                ->invoiceExistedMultipleWebsite($incrementId, $order->getStore()->getWebsiteId());
        } else {
            $invoiceExisted = $this->syncOrder->invoiceExisted($incrementId);
        }
        if ($invoiceExisted && $invoiceExisted['Status'] == 'PAID') {
            $log = $this->logFactory->create();
            $log->addData([
                'type' => 'InvoiceToInvoice',
                'entity_id' => $incrementId,
                'dequeue_time' => time(),
                'status' => Status::SUCCESS_STATUS,
                'xero_id' => $invoiceExisted['InvoiceID'],
                'msg' => 'Order is already paid/closed, a record update process was not performed.',
                'xml_log_id' => $this->helper->getIdInCollectionByMagentoId($incrementId, $this->type)
            ])->save();
            return '';
        }

        $xml = '<Invoice>';
        $xml .= '<InvoiceNumber>' . $this->helper->getPrefixId($invoice->getIncrementId()) . '</InvoiceNumber>';
        $xml .= '<Reference>'.$order->getIncrementId().'</Reference>';
        $xml .= $this->syncOrder->getCustomerXml($invoice->getOrder());
        $xml .= '<Type>ACCREC</Type>';
        $xml .= '<CurrencyCode>' . $invoice->getOrderCurrencyCode() . '</CurrencyCode>';
        $xml .= '<Date>' . $createdAt . '</Date>';
        $xml .= '<DueDate>' . $updatedAt . '</DueDate>';
        $xml .= '<Status>AUTHORISED</Status>';
        $xml .= '<LineAmountTypes>'.$this->syncOrder->getTaxType().'</LineAmountTypes>';
        $xml .= $this->getItemsInvoiceXml($invoice);
        $xml .= '</Invoice>';
        $this->syncPayment->addRecord($invoice);

        return $xml;
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return string
     */
    protected function getItemsInvoiceXml($invoice)
    {
        $order = $invoice->getOrder();
        return $this->syncOrder->getItemsOrderXml($order);
    }

    /**
     * @param $invoice
     * @throws \Exception
     */
    public function guestToXml($invoice)
    {
        $order = $invoice->getOrder();
        $this->syncOrder->guestToXml($order);
    }

    protected function getContactsOnXero()
    {
        $varPath = $this->_directoryList->getPath('var');
        $path = $varPath . '/xerocontact/contact.txt';
        try {
            return json_decode($this->_file->fileGetContents($path), true);
        } catch (FileSystemException $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)
                ->debug('Xero Save Customer Exception: '.$exception->getMessage());
        }
        return [];
    }

    public function syncAllGuestToXero()
    {
//        $this->syncOrder->setCustomerXmlToSync($this->customerXmlToSync);
        $this->syncOrder->syncAllGuestToXero();
    }

    public function _additionalSync($additionalXml)
    {
        $this->syncPayments();
    }

    public function syncPayments()
    {
        $this->syncPayment->syncPayments();
        $this->syncPayment->unsetRecords();
    }

    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return string
     */
    protected function getTaxXml($invoice)
    {
        $taxAmount = $invoice->getTaxAmount() > 0 ? $invoice->getTaxAmount() : 0.0;
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
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return string
     */
    protected function getShippingXml($invoice)
    {
        if ($invoice->getOrder()->getIsVirtual()) {
            return "";
        }
        if ($this->syncOrder->getTaxType() == "Inclusive") {
            $shippingAmount = $invoice->getShippingInclTax();
        } else {
            $shippingAmount = $invoice->getShippingAmount();
        }
        $shippingAmount = $shippingAmount ? : 0.0;
        $amount = $invoice->getShippingTaxAmount() > 0 ? $invoice->getShippingTaxAmount() : 0.0;
        $shippingAccountId = "";
        if ($this->helper->isMultipleWebsiteEnable()) {
            $shippingAccountId = $this->helper->getConfig('magenest_xero_config/xero_account/shipping_id');
        }
        return $this->syncOrder->getFormatXmlShipping($shippingAmount, $amount, $shippingAccountId);
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

    /**
     * @param $xml
     * @param string $method
     * @return string
     * @throws \Exception
     */
    public function syncData($xml, $method = 'POST')
    {
        $this->syncOrder->syncMissingCustomer();
        $this->syncOrder->syncMissingProduct();
        return parent::syncData($xml, $method); // TODO: Change the autogenerated stub
    }

    public function getItemPrice($item)
    {
        if ($this->syncOrder->getTaxType() == "Inclusive") {
            return $item->getPriceInclTax();
        } else {
            return $item->getPrice();
        }
    }
}
