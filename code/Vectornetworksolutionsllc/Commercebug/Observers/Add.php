<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Observers;

class Add extends AbstractObserver
{
    protected $logger;
    protected $pulsestormCommercebugLogFactory;
    protected $renderedData;
    protected $logFactory;
    protected $jsonFactory;
    protected $productMetaData;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Vectornetworksolutionsllc\Commercebug\Model\LogFactory $logFactory,
        \Vectornetworksolutionsllc\Commercebug\Model\RenderedData $renderedData,        
        \Magento\Developer\Helper\Data $developerHelper,
        \Vectornetworksolutionsllc\Commercebug\Renderer\JsonFactory $jsonFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData                
    )
    {
        $this->productMetaData  = $productMetaData;
        $this->jsonFactory      = $jsonFactory;
        $this->developerHelper  = $developerHelper;
        $this->logFactory       = $logFactory;
        $this->pulsestormCommercebugLogFactory = $logFactory;
        $this->logger = $logger;
        $this->renderedData = $renderedData;
        return parent::__construct($developerHelper);
    }
    
    protected function _execute(\Magento\Framework\Event\Observer $observer)
    {
        return $this->addToHtmlPage($observer);
    }
    
    protected function getCommerceBugDataFromLog($request)
    {
        $id = $request->getParam('id');
        $log = $this->logFactory->create()->load($id);
        $array = json_decode($log->getJsonLog(), true);
        return $array;
    }
    
    protected function isDirectLogAccess($request)
    {
        return 
            strpos($request->getOriginalPathInfo(), 'pulsestorm_commercebug/viewlog') !== false;
    }
    
    protected function getCommerceBugData($observer)
    {
        $request = $observer->getRequest();
        if($this->isDirectLogAccess($request))
        {
            return $this->getCommerceBugDataFromLog($request);
        }

        $cb_data = \Vectornetworksolutionsllc\Commercebug\Model\All::asData();
        $cb_data['server']  = $_SERVER;
        $cb_data['metadata'] = [
            'version'=>$this->productMetaData->getVersion()
        ];
        //add to the renderData singleton in case we need/want to access
        //the data somewhere else
        $rendered_data      = $this->renderedData->setData($cb_data);
        return $cb_data;
    }
    
    protected function logData($data, $observer)
    {
        $request = $observer->getRequest();
        if($this->shouldLogData($request))
        {
            return;
        }
        $model = $this->pulsestormCommercebugLogFactory->create()
            ->logData($data);    
    }
    
    protected function shouldLogData($request)
    {
        return $this->isDirectLogAccess($request);
    }
    
    protected function renderScriptTag($data)
    {
        $renderer   = $this->jsonFactory->create();
        $renderer->setData($data);                
        $script     = $renderer->render();
        $finalData  = $renderer->getFinalData();
        
        return [$script,$finalData];
        // return $script;  
    }
    
    protected function addScriptTagToPage($script, $observer)
    {
        $response           = $observer->getResponse();
        $body               = $response->getBody();                 
        $new_body           = str_replace('</body>', 
            $script . '</body>',  $body);
        $response->setBody($new_body);     
    }
    
    public function addToHtmlPage($observer)
    {                
        //render the json that's added to page via script tags               
        $cb_data            = $this->getCommerceBugData($observer);                

        //add the data to the rolling log table
        //$this->logData($cb_data, $observer);

        //renders the actual script tag to add to the HTML page
        list($script, $finalData) = $this->renderScriptTag($cb_data);
        
        //add the data to the rolling log table
        $this->logData($finalData, $observer);

        $header_accept = $observer->getRequest()->getHeader('Accept');
        if(strpos($header_accept, 'text/html') === false)
        {
            return;
        }
        //adds the script tag to the HTML page
        $this->addScriptTagToPage($script, $observer);   
    }
}
