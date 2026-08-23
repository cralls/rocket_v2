<?php

namespace VNS\Ups\Cron;

class TrackPackage
{
    protected $tracker;
    protected $transportBuilder;
    protected $storeManager;
    protected $templateFactory;
    protected $scopeConfig;
    protected $appState;
    
    public function __construct(
        \VNS\Ups\Model\Tracker $tracker,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $appState        
        ) {
            $this->tracker = $tracker;
            $this->transportBuilder = $transportBuilder;
            $this->storeManager = $storeManager;
            $this->assetRepo = $assetRepo;
            $this->scopeConfig = $scopeConfig;
            $this->appState = $appState;
    }
    
    public function execute()
    {
        try {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); // Set the area code
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Area code is already set or other error
        }
        $trackingResults = $this->tracker->queryUpsApi();
        foreach ($trackingResults as $result) {
            //echo "Response is \r\n"; print_r($result);
            if($result['response'] == "FS") { // Delivered
                if($this->sendEmail($result['customer_email'], '49', $result)) {
                    $result['track']->setDelivered(1);
                    $result['track']->save();
                }
            }elseif(($result['response'] == "OT") && $result['track']->getOutForDelivery() == 0) { // Out for delivery
                if($this->sendEmail($result['customer_email'], '48', $result)) {
                    $result['track']->setOutForDelivery(1);
                    $result['track']->save();
                }
                
            }
            
            $currentDateTime = date('Y-m-d H:i:s');
            $logMessage = "[$currentDateTime] Tracking Log: " . json_encode($result) . PHP_EOL;
            error_log($logMessage, 3, BP . '/var/log/tracking.log');
        }
        
        return $this;
    }
    
    private function sendEmail($recipientEmail, $templateIdentifier, $templateVars)
    {
        /*if($recipientEmail == 'rivadeloray@gmail.com') {
            $recipientEmail = 'cralls@vectorns.com';
        } else {
            return false;
        }
        echo "Sending email to $recipientEmail\r\n";*/
        
        $storeId = $this->storeManager->getStore()->getId();
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $storeId,
        ];
        
        $transport = $this->transportBuilder
        ->setTemplateIdentifier($templateIdentifier)
        ->setTemplateOptions($templateOptions)
        ->setTemplateVars($templateVars)
        ->setFrom(['name'=>'Rocket Science Sports','email'=>'online.orders@rocketsciencesports.com'])
        ->addTo($recipientEmail)
        ->getTransport();
        
        try {
            $transport->sendMessage();
            return true;
        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            
        }
    }
}
