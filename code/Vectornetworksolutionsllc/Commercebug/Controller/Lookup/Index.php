<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Controller\Lookup;
use ReflectionClass;
use Vectornetworksolutionsllc\Commercebug\Model\All;

class Index extends \Vectornetworksolutionsllc\Commercebug\Controller\AbstractController
{    
    protected $objectManagerExceptions=[];
    
    protected function checkClassConstructorArgumentsForInterfaces($class)
    {
        $r = new ReflectionClass($class);
        $construct = $this->getConstructorMethodFromReflectedClass($r);
        if(!$construct)
        {
            return;
        }
        
        foreach($construct->getParameters() as $param)
        {
            $paramClass = $param->getClass();
            if(!$paramClass){continue;}
            $preference = $this->objectManagerConfig->getPreference($paramClass->getName());
            
            $rp = new ReflectionClass($preference);
            if($rp->isInterface())
            {
                throw new \Exception("Class $class has a constructor parameter ({$rp->getName()}) that is an interface with an unconfigured di.xml preference.");
            }
        }
    }
    
    protected function getClassSingletonFromObjectManagerObject($class)
    {
        $om_class = false;
        try
        {
            $this->checkClassConstructorArgumentsForInterfaces($class);
            $om_class = $this->objectManager->get($class);  
        }
        catch(\Exception $e)
        {
            $om_class = false;
            $this->objectManagerExceptions[] = $e;
        }
        return $om_class;
    }
    
    protected function getClassFromManager($class)
    {
        $class_from_manager = false;        
        if(!$this->shouldSkipInstantiation($class))
        {       
            $om_class = $this->getClassSingletonFromObjectManagerObject($class);
            if($om_class)
            {
                $class_from_manager = All::getClass($om_class);
            }
        }            
        return $class_from_manager;  
    }
    
    protected function getClassFromRequest()
    {
        $class = false;
        if(array_key_exists('lookup', $_POST))
        {
            $class  = trim($_POST['lookup']);
        }    
        return $class;
    }
    
    protected function getReflectedClass($class)
    {
        if($class)
        {
            return new ReflectionClass($class);            
        }
        return false;
    }
    
    protected function getConstructorMethodFromReflectedClass($r)
    {
        $method = false;
        try
        {
            $method = $r->getMethod('__construct');
        }
        catch(\Exception $e)
        {
            $method = false;
        }
        return $method;    
    }
    
    protected function assignViewVarsConstructorParams($class, $method)
    {
        if($class && $method)
        {
            $r = new ReflectionClass($class);
            $method = $r->getMethod('__construct');
            $params = [];
            foreach($method->getParameters() as $param)
            {
                if($param->getClass())
                {
                    $params[$param->getName()] = $param->getClass()->getName();
                }
                else
                {
                    $params[$param->getName()] = 'UnTypedOrArray';
                }
            }
            $this->viewVars->setConstructorParams($params);
        }     
    }
    
    protected function assignViewVars($class, $class_from_manager, $r, $rm)
    {
        if($class)
        {
            $this->viewVars->setClassToLookupName($class);
            $this->viewVars->setClassToLookupPath($r->getFileName());
        }
        
        if($class_from_manager)
        {
            $this->viewVars->setObjectManagerClassName($class_from_manager);
            $this->viewVars->setObjectManagerClassPath($rm->getFileName());
        }
                
        $this->viewVars->setConstructorParams([]);                    
        $method = $this->getConstructorMethodFromReflectedClass($r);                             
        $this->assignViewVarsConstructorParams($class, $method);     
        
        $this->viewVars->setObjectManagerErrors($this->objectManagerExceptions);             
    }
    
    /**
     * Index action
     *
     * @return $this
     */
    public function execute()
    {
        $class = $this->getClassFromRequest();        
        $class_from_manager = $this->getClassFromManager($class);
        
        $r     = $this->getReflectedClass($class);
        $rm    = $this->getReflectedClass($class_from_manager);
        
        $this->assignViewVars($class, $class_from_manager, $r, $rm);

        $resultPage = $this->resultPageFactory->create();                
        
        return $this->resultPageFactory->create();                
    }
}
