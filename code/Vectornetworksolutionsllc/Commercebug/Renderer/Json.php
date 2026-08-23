<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Renderer;
use Vectornetworksolutionsllc\Commercebug\Model\All;
use ReflectionClass;

class Json
{

    protected $cacheManager;
    protected $request;
    protected $data;
    protected $finalData;
    public function __construct(
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\App\RequestInterface $requestInterface)
    {
        $this->request      = $requestInterface;
        $this->cacheManager = $cacheManager;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    /**
    * A mess, and a mistake, and should be refactored out
    * cleaning the data is good -- actualyl resetting data, not so much
    */    
    public function clean($data)
    {
        $data = $data ? $data : [];
        if(is_string($data))
        {
            $data = json_decode($data, true);
        }
        foreach($data as $key=>$value)
        {
            if($key === 'invoked_observers')
            {
                $data[$key] = array_filter($value, function($val){                    
                    if(!isset($val['instance'])){
                        return;
                    }
                    return strpos($val['instance'], 'Vectornetworksolutionsllc\\Commercebug') === false;
                });
            }
            
            if(in_array($key,['models','collections']) && !$value)
            {
                $data[$key] = All::getCollectionOfInformationFor($key);
            }

            if(in_array($key,['models','collections']) && is_array($value))
            {
                $new = [];
                foreach($data[$key] as $index=>$object)
                {
                    if(!is_object($object)){ continue;}
                    $r = new ReflectionClass($object);
                    $new[All::getClass($object)] = ['file'=>$r->getFilename()];
                }
                if(count($new))
                {
                    $data[$key] = $new;
                }
            }
                        
            if($key === 'blocks' && !$value)
            {
                $data[$key] = All::getCollectionOfInformationForBlocks();
            }            
            
            if(in_array($key,['blocks']) && is_array($value))
            {
                $new = [];
                foreach($data[$key] as $index=>$object)
                {                 
                    if(!is_object($object)){continue;}   
                    $r = new ReflectionClass($object);
                    $new[All::getClass($object)] = [
                        'className'=>All::getClass($object),
                        'classFile'=>$r->getFilename(),
                        'name'=>$object->getNameInLayout(),
                        'template'=>$object->getTemplateFile()
                    ];
                }
                if(count($new))
                {
                    $data[$key] = $new;
                }
            }
            
            if($key === 'controllers' && !$value)
            {
                $data[$key] = All::getCollectionOfInformationFor($key);
                $data[$key] = All::normalizeControllerInterceptors($data[$key]);
            } 
            
            if(in_array($key,['controllers']) && is_array($value))
            {
                $new = [];
                foreach($data[$key] as $index=>$object)
                {
                    if(!is_object($object)){ continue;}                
                    $tmp = [];
                    $r = new ReflectionClass($object);
                    $tmp['interceptor'] = [
                        'className'=>$r->getName(),
                        'file'=>$r->getFilename()
                    ];
                    $tmp['class'] = [
                        'className'=>$r->getParentClass()->getName(),
                        'file'=>$r->getParentClass()->getFilename()
                    ];        
                    $new[$r->getName()] = $tmp;            
                }
                if(count($new))
                {
                    $data[$key] = $new;
                }
            }                            
        }

        if(!isset($data['layouts']))
        {
            $data['layouts'] = [
                'graph'=> \Vectornetworksolutionsllc\Commercebug\Plugins\MagentoFrameworkViewLayout::renderGraph(),
                'nonce'=> md5 (md5( date('Y-m-d', strToTime("-0day")) ) . 'not a drill'),
                'full_page_cache' => $this->getFullPageCacheStatus()
            ];
        }        
        
        // if(!isset($data['other-files']))
        // {
        //     $data['other-files'] = get_included_files();
        // }
        
        foreach(['blocks','collections','controllers',
        'dispatched_events','handles','invoked_observers',
        'layouts','models','other-files','server'] as $key)
        {
            if(!isset($data[$key]))
            {
                $data[$key] = [];
            }
        }
        return $data;   
    }
    
    protected function getFullPageCacheStatus()
    {
        $status = $this->cacheManager->getStatus();
        if(isset($status['full_page']))
        {
            return $status['full_page'];
        } 
        return 0;
    }
    
    public function getFinalData()
    {
        return $this->finalData;
    }
    
    public function render()
    {
        $this->finalData = $this->clean($this->data);        
                
        return '<script type="text/javascript">' . 
        'pulsestorm_commerbug_json = '           . 
        json_encode($this->finalData, 
            JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS) . 
            ';'                                  .
        '</script>';
        
    }
}