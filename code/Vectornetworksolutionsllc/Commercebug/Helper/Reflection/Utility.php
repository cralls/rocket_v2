<?php
namespace Vectornetworksolutionsllc\Commercebug\Helper\Reflection;
use ReflectionClass;
class Utility
{
    public function getConstructorParamsFromClassFile($classFile)
    {
        $class = $this->getClassNameFromFile($classFile);
        $reflection_class = new ReflectionClass($class);
        return $reflection_class->getMethod('__construct')->getParameters();
    }
    
    public function getClassNameFromFile($class)
    {
        $contents = file_get_contents($class);
        preg_match('%namespace(.+?);%',$contents, $matches);
        $namespace = $matches[1];
        
        preg_match('%class\s+?([a-zA-Z_]+)%',$contents, $matches);
        $class = $matches[1];
        $class = trim($namespace) . '\\' . $class;
        return $class;
    }
    
}