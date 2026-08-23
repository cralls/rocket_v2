<?php 

namespace VNS\Ups\Model;

class Tracker extends \Magento\Framework\Model\AbstractModel
{
    protected $shipmentTrackCollectionFactory;
    
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $shipmentTrackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $shipmentItemCollectionFactory
        ) {
            $this->shipmentTrackCollectionFactory = $shipmentTrackCollectionFactory;
            $this->shipmentItemCollectionFactory = $shipmentItemCollectionFactory;
    }
    
    public function getUndeliveredTrackingNumbers()
    {
        $collection = $this->shipmentTrackCollectionFactory->create()
        ->addFieldToFilter('delivered', ['eq' => 0])
        ->distinct(true);
        
        $trackingInfo = [];
        foreach ($collection as $track) {
            
            // Fetch the shipment items using the shipment ID
            $shipmentItemsCollection = $this->shipmentItemCollectionFactory->create()
            ->addFieldToFilter('parent_id', ['eq' => $track->getParentId()]);
            
            $itemsInfo = [];
            foreach ($shipmentItemsCollection as $item) {
                $itemsInfo[] = [
                    'name' => $item->getName(),
                    'qty' => $item->getQty(),
                    'sku' => $item->getSku(),
                ];
            }
            
            $itemsHtml = '<table border="1" cellpadding="10" align="center"><thead><tr><th>Item Name</th><th>Quantity</th><th>SKU</th></tr></thead><tbody>';
            
            foreach ($itemsInfo as $_item) {
                $itemsHtml .= "<tr><td>{$_item['name']}</td><td>{$_item['qty']}</td><td>{$_item['sku']}</td></tr>";
            }
            
            $itemsHtml .= '</tbody></table>';
            
            $parentShipment = $track->getShipment();
            if ($parentShipment) {
                $order = $parentShipment->getOrder();
                if ($order) {
                    $trackingInfo[] = [
                        'track_number' => $track->getTrackNumber(),
                        'customer_email' => $order->getCustomerEmail(),
                        'order_id' => $order->getId(),
                        'track' => $track,
                        'delivered' => $track->getDelivered(),
                        'out_for_delivery' => $track->getOutForDelivery(),
                        'items' => $itemsHtml
                    ];
                }
            }
        }
        
        return $trackingInfo;
    }
    
    
    private function getUpsAccessToken()
    {
        $curl = curl_init();
        
        $payload = "grant_type=client_credentials";
        
        $username = 'fGdENNjwiEAfQqFiS3ThzaZDH8NvXuJ0EBTNPEBcIzkarhHn'; // Replace with your UPS username
        $password = 'Uv36HUN67DhAOaW1wRceicCLAdW3hFiAYXzbTcOR2MAsA4BYfBMuYJ6nUy2Qaxrh'; // Replace with your UPS password
        
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
                "x-merchant-id: 8533YW", // Replace with your merchant ID if required
                "Authorization: Basic " . base64_encode($username . ":" . $password)
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_URL => "https://onlinetools.ups.com/security/v1/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
        ]);
        
        $response = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new \Exception("cURL Error #: " . $error);
        } else {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }
    }
    
    
    
    public function queryUpsApi()
    {
        // Fetch undelivered tracking numbers
        $trackingInfos = $this->getUndeliveredTrackingNumbers();
        
        // Fetch the UPS access token
        $token = $this->getUpsAccessToken();
        
        // Array to hold API responses
        $apiResponses = [];
        
        foreach ($trackingInfos as $info) {
            $trackingNumber = trim($info['track_number']);
            $customerEmail = trim($info['customer_email']);
            
            // Setup query parameters
            $query = [
                "locale" => "en_US",
                "returnMilestones" => "false",
                "returnSignature" => "false"
            ];
            
            // Initialize cURL session
            $curl = curl_init();
            
            // Generate a unique transaction ID
            $transId = uniqid('trans_');
            
            $curlOptions = [
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $token,
                    "transactionSrc: Magento2_UPS_Tracking",
                    "transId: " . $transId
                ],
                CURLOPT_URL => "https://onlinetools.ups.com/api/track/v1/details/" . $trackingNumber . "?" . http_build_query($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "GET",
            ];
            
            curl_setopt_array($curl, $curlOptions);
            
            // Execute cURL session
            $response = curl_exec($curl);
            
            // Check if valid json response
            /*$response = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON Decode Error: " . json_last_error_msg());
            }*/
            
            $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpStatusCode != 200) {
                $currentDateTime = date('Y-m-d H:i:s');
                $logMessage = "[$currentDateTime] Tracking Log: " . json_encode($result) . PHP_EOL;
                error_log($logMessage, 3, BP . '/var/log/tracking.log');
                continue;
            }
            
            /*echo "### curlOptions ###\r\n ";
            print_r($curlOptions);
            echo "\r\n\r\n### Response ###\r\n";  
            print_r(json_decode($response));
            echo "\r\n\r\n";*/
            
            $error = curl_error($curl);
            
            // Close cURL session
            curl_close($curl);
            
            if ($error) {
                // Handle error - log or throw exception
                // For example: throw new \Exception("cURL Error #: " . $error);
            } else {
                $responseArray = json_decode($response);
                //error_log("[".date('Y-m-d H:i:s')."] - RESPONSE ".print_r($responseArray, 1)."\r\n", 3, BP . '/var/log/tracking.log');
                $responseStatus = isset($responseArray->trackResponse->shipment[0]->package[0]->activity[0]->status->code) ? $responseArray->trackResponse->shipment[0]->package[0]->activity[0]->status->code : '';
                $apiResponses[$trackingNumber] = [
                    'track_number' => $trackingNumber,
                    'customer_email' => $customerEmail,
                    'response' => $responseStatus,
                    'track' => $info['track'],
                    'delivered' => $info['delivered'],
                    'out_for_delivery' => $info['out_for_delivery'],
                    'items' => $info['items']
                ];
            }
        }
        
        return $apiResponses;
    }
    
    
    
}