<?php
namespace Vectornetworksolutionsllc\Commercebug\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Scandoubleparam extends Command
{
    public function __construct(
        \Vectornetworksolutionsllc\Commercebug\Helper\Reflection\Utility $reflectionUtility,
        $name = null)
    {        
        $this->reflectionUtility = $reflectionUtility;
        return parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName("ps:cb:scan:double-param");
        $this->setDescription("Scans constructor for double params.")
        ->addArgument(
            'class',
            InputArgument::REQUIRED,
            'Class file to Scan');
        parent::configure();
    }

    protected function reportOnDupes($array, $name, $output)
    {
        $without_dupes = array_unique($array);
        if($without_dupes !== $array)
        {            
            $diff = array_diff_assoc($array, $without_dupes);
            $output->writeln($name . ' has dupes: ' . implode(',',$diff));
            return;
        }
        $output->writeln($name . ' is free of dupes.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $params = $this->reflectionUtility->getConstructorParamsFromClassFile(
            $input->getArgument('class')
        );
        
        $names = array_map(function($ref){
            return $ref->getName();
        }, $params);
        
        $types = array_map(function($ref){
            $o = $ref->getClass();
            if(!$o){return null;}
            return $o->getName();
        }, $params);                
        $types = array_filter($types);        
        
        $this->reportOnDupes($names, 'Param Names', $output);
        $this->reportOnDupes($types, 'Param Types', $output);
        $output->writeln("Scan complete");  
    }
} 