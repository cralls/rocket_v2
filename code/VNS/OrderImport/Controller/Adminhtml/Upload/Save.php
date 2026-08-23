<?php

namespace VNS\OrderImport\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Directory\Model\RegionFactory;

class Save extends Action
{
    
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        QuoteManagement $quoteManagement,
        QuoteFactory $quoteFactory,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        AddressInterface $addressInterface,
        DirectoryList $directoryList,
        RegionFactory $regionFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
        ) {
            parent::__construct($context);
            $this->orderFactory = $orderFactory;
            $this->quoteManagement = $quoteManagement;
            $this->quoteFactory = $quoteFactory;
            $this->productRepository = $productRepository;
            $this->customerRepository = $customerRepository;
            $this->customerFactory = $customerFactory;
            $this->storeManager = $storeManager;
            $this->addressInterface = $addressInterface;
            $this->directoryList = $directoryList;
            $this->regionFactory = $regionFactory;
            $this->orderSender = $orderSender;
    }


    /**
     * Execute method.
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['name']) {
                $basePath = $this->directoryList->getPath(DirectoryList::MEDIA) . '/import_orders/';
                $targetPath = $basePath . $_FILES['csv_file']['name'];
                move_uploaded_file($_FILES['csv_file']['tmp_name'], $targetPath);
                
                $orders = []; // Initialize an array to hold the order data
                
                if (($handle = fopen($targetPath, "r")) !== FALSE) {
                    $header = fgetcsv($handle); // Extract header row
                    while (($row = fgetcsv($handle, 0)) !== FALSE) {
                        $rowData = array_combine($header, $row);
						if (isset($rowData['Name'])) {
            				$name = $rowData['Name'];
        				} else {
            				throw new \Exception("The 'Name' column is missing or misformatted.".print_r($rowData, 1));
        				}
                        
                        // Initialize order if not already done
                        if (!isset($orders[$name])) {
                            $orders[$name] = [
                                'items' => [],
                                'shipping_address' => [] // Prepare to store shipping address
                            ];
                        }
                        
                        // Store item details if Vendor is "Rocket Science"
                        if (isset($rowData['Vendor']) && $rowData['Vendor'] === "Rocket Science") {
                            $orders[$name]['items'][] = $rowData;
                        }
                        
                        // Capture shipping address if any field is filled
                        if (!empty($rowData['Shipping Name']) || !empty($rowData['Shipping Street']) || !empty($rowData['Shipping City'])) {
                            $orders[$name]['shipping_address'] = [
                                'Shipping Name' => $rowData['Shipping Name'],
                                'Shipping Street' => $rowData['Shipping Street'],
                                'Shipping Address1' => $rowData['Shipping Address1'],
                                'Shipping Address2' => $rowData['Shipping Address2'],
                                'Shipping Company' => $rowData['Shipping Company'],
                                'Shipping City' => $rowData['Shipping City'],
                                'Shipping Zip' => $rowData['Shipping Zip'],
                                'Shipping Province' => $rowData['Shipping Province'],
                                'Shipping Country' => $rowData['Shipping Country'],
                                'Shipping Phone' => $rowData['Shipping Phone'],
                            ];
                        }
                    }
                    fclose($handle);
                    
                    // Pretty print the orders array
                    //echo "<pre>" . print_r($orders, true) . "</pre>"; die();
                    
                    // Create orders with imported data
                    $this->createOrder($orders);
                    
                    // Success message (for actual processing logic)
                    $this->messageManager->addSuccessMessage(__('Orders have been processed.'));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
    
    

    /**
     * Simplified example of an order creation method.
     * This method should be expanded to handle all aspects of order creation based on your requirements.
     *
     * @param array $data CSV row data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createOrder($orders)
    {
        foreach ($orders as $orderData) {
            try {
                
                if(!isset($orderData['items']['0'])) continue;
                
                $store = $this->storeManager->getStore();
                $storeId = $store->getId();
                $websiteId = $store->getWebsiteId();
                
                $formattedAddress = $this->formatAddress($orderData['shipping_address']);
                
                $customer = $this->ensureCustomer($orderData['items'][0]['Email'], $formattedAddress, $websiteId);
                
                if ($customer->getGroupId() != 3) {
                    $customer->setGroupId(3); // Set to retailer group ID
                    $this->customerRepository->save($customer);
                }
                
                // Prepare the quote
                $quote = $this->quoteFactory->create();
                $quote->setStore($store);
                $quote->setCurrency();
                $quote->assignCustomer($customer); // Existing customer
                
                // Add products to quote
                foreach ($orderData['items'] as $item) {
                    $product = $this->productRepository->get($item['Lineitem sku']);
                    $quote->addProduct($product, intval($item['Lineitem quantity']));
                }
                $quote->setStoreId($storeId); // Reinforce the store ID on the quote
                
                $quote->getBillingAddress()->addData($formattedAddress);
                $quote->getShippingAddress()->addData($formattedAddress);
                $quote->getShippingAddress()->setCollectShippingRates(true)
                ->collectShippingRates();
                
                // Set shipping method and payment method
                $shippingAddress = $quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('flatrate_flatrate'); // Example shipping method
                
                $quote->setPaymentMethod('checkmo'); // Example payment method
                $quote->setInventoryProcessed(false); // Prevents inventory decrement
                
                $quote->collectTotals();
                
                // Explicitly associate the quote with the payment just before importing payment data.
                $payment = $quote->getPayment();
                $payment->setQuote($quote);
                
                // Now attempt to set the payment method.
                $payment->importData(['method' => 'checkmo']);
                
                // Set Sales Order Payment
                //$quote->getPayment()->importData(['method' => 'checkmo']);
                
                // Collect Totals & Save Quote
                $quote->collectTotals()->save();
                
                // Create Order From Quote
                $order = $this->quoteManagement->submit($quote);
                
                if ($order->getId()) {
                    // Set the custom column 'shipping_gift' to 1
                    $order->setData('shipping_gift', 1);
                    $order->setData('team_portal', 323);
                    $order->save();
                    
                    try {
                        $order->save();
                        
                        // Send the order confirmation email
                        if (!$order->getEmailSent()) {
                            $this->orderSender->send($order);
                            $order->setEmailSent(true);
                            $order->save(); // Save the order again after setting the email sent flag
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Failed to send the order email. Error: %1', $e->getMessage()));
                    }
                }
                
                $order->setEmailSent(0);
                
                if ($order->getEntityId()) {
                    // Success, log order creation, or perform additional actions as needed
                } else {
                    // Log failure or take appropriate action
                    $this->messageManager->addError(__('The order could not be created for '.$orderData['items'][0]['Email']));
                }
            } catch (\Exception $e) {
                // Log the exception and display an error message
                $this->messageManager->addError(__('An error occurred while creating the order for '.$orderData['items'][0]['Email'].'. Error: %1', $e->getMessage()));
            }
        }
    }
    
    public function ensureCustomer($email, $formattedAddress, $websiteId)
    {
        try {
            // Check if customer exists
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Customer does not exist, create a new one
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId)
            ->setEmail($email)
            ->setFirstname($formattedAddress['firstname'])
            ->setLastname($formattedAddress['lastname'])
            // Add any other necessary data
            ->save();
            $customer = $this->customerRepository->get($email, $websiteId);
        }
        return $customer;
    }
    
    protected function formatAddress($address)
    {
        // Split the full name into first and last names
        $fullName = explode(' ', $address['Shipping Name'], 2);
        $firstName = $fullName[0];
        $lastName = isset($fullName[1]) ? $fullName[1] : '.';
        
        // Attempt to fetch the region_id based on the Shipping Province (assuming US state abbreviations)
        $regionId = $this->getRegionIdByCode($address['Shipping Province'], 'US');
        
        $formattedAddress = [
            'firstname' => $firstName,
            'lastname' => $lastName,
            'street' => [
                $address['Shipping Address1'],
                $address['Shipping Address2'], // This can be empty and Magento will handle it appropriately
            ],
            'city' => $address['Shipping City'],
            'postcode' => $address['Shipping Zip'],
            'telephone' => $address['Shipping Phone'],
            'country_id' => $address['Shipping Country'], // Ensure this is a valid two-letter country code
        ];
        
        
        if ($regionId) {
            $formattedAddress['region_id'] = $regionId;
        } else {
            $formattedAddress['region'] = $address['Shipping Province'];
        }
        
        return $formattedAddress;
        
    }
    
    // Pseudocode to fetch region_id from region code and country code
    protected function getRegionIdByCode($regionCode, $countryCode)
    {
        $region = $this->regionFactory->create()->loadByCode($regionCode, $countryCode);
        return $region ? $region->getId() : null;
    }
    
}
